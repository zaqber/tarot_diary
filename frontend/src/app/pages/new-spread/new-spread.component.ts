import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { SpreadService } from '../../services/spread.service';
import { TarotCardService } from '../../services/tarot-card.service';
import { SuitService } from '../../services/suit.service';
import { TarotCard } from '../../models/tarot-card.model';
import { SpreadReadingDetail } from '../../models/spread-reading.model';
import { Suit } from '../../services/suit.service';
import { getTodayDateStringInTaipei } from '../../utils/date.util';

interface SlotCard {
  position_number: number;
  card: TarotCard | null;
  /** 是否為逆位 */
  isReversed: boolean;
  /** 符合當天狀態的 tag id 列表（點選後由後端紀錄） */
  selectedTagIds: number[];
}

@Component({
  selector: 'app-new-spread',
  templateUrl: './new-spread.component.html',
  styleUrls: ['./new-spread.component.css']
})
export class NewSpreadComponent implements OnInit {
  readingId: number | null = null;
  slots: SlotCard[] = [
    { position_number: 1, card: null, isReversed: false, selectedTagIds: [] },
    { position_number: 2, card: null, isReversed: false, selectedTagIds: [] },
    { position_number: 3, card: null, isReversed: false, selectedTagIds: [] }
  ];
  readingDetail: SpreadReadingDetail | null = null;

  /** 自動抽牌中 */
  autoDrawing = false;
  /** 手動選牌：正在選的格子 1|2|3 */
  manualSlot: 1 | 2 | 3 | null = null;
  /** 手動選牌步驟：先選花色 → 選牌 → 選正逆位 */
  manualStep: 'suit' | 'card' | 'orientation' = 'suit';
  /** 手動選牌時暫存的牌（選完牌後要選正/逆位） */
  pendingCard: TarotCard | null = null;
  /** 花色選項（含「大牌」） */
  suitOptions: Array<{ id: number | null; name_zh: string }> = [];
  /** 依花色/大牌篩選出的牌列表，供使用者點選 */
  cardsForPick: TarotCard[] = [];
  /** 載入中 */
  loadingCards = false;
  errorMessage = '';

  constructor(
    private spreadService: SpreadService,
    private tarotCardService: TarotCardService,
    private suitService: SuitService,
    private route: ActivatedRoute
  ) {}

  ngOnInit(): void {
    this.loadSuitOptions();
    this.loadInitialReading();
  }

  /** 進入頁面時：若有 query readingId 則載入該筆；否則載入今日牌陣 */
  private loadInitialReading(): void {
    const readingId = this.route.snapshot.queryParamMap.get('readingId');
    if (readingId) {
      const id = +readingId;
      if (!isNaN(id)) {
        this.spreadService.getSpreadReading(id).subscribe({
          next: (res: any) => this.applyReadingDetail(res.data ?? res),
          error: () => {}
        });
        return;
      }
    }
    this.spreadService.getTodayReading().subscribe({
      next: (res: any) => {
        const data = res.data ?? res;
        if (data?.reading_date && data.reading_date !== getTodayDateStringInTaipei()) {
          // 回傳的不是「今天」的紀錄（例如時區差異），不套用，顯示空白牌陣
          return;
        }
        this.applyReadingDetail(data);
      },
      error: () => {}
    });
  }

  /** 畫面上要顯示的日期（今日或該筆牌陣的日期），以台北時間為準 */
  get displayDate(): string {
    const dateStr = this.readingDetail?.reading_date ?? getTodayDateStringInTaipei();
    if (!dateStr) return '';
    const [y, m, d] = dateStr.split('-');
    return `${y}年${parseInt(m, 10)}月${parseInt(d, 10)}日`;
  }

  private applyReadingDetail(detail: SpreadReadingDetail): void {
    this.readingDetail = detail;
    this.readingId = detail.id;
    this.slots = [1, 2, 3].map(pos => {
      const sc = detail.spread_cards?.find(c => c.position_number === pos);
      return {
        position_number: pos,
        card: sc?.card ?? null,
        isReversed: sc?.is_reversed ?? false,
        selectedTagIds: sc?.selected_tag_ids ?? []
      };
    });
  }

  get allSlotsFilled(): boolean {
    return this.slots.every(s => s.card != null);
  }

  get showAboutMyDay(): boolean {
    return this.allSlotsFilled && this.readingDetail != null;
  }

  private loadSuitOptions(): void {
    this.suitService.getSuits().subscribe({
      next: (res: any) => {
        const data = res.data ?? res;
        const list = Array.isArray(data) ? data : [];
        this.suitOptions = [
          { id: null, name_zh: '大牌' },
          ...list.map((s: Suit) => ({ id: s.id, name_zh: s.name_zh }))
        ];
      },
      error: () => {
        this.suitOptions = [
          { id: null, name_zh: '大牌' },
          { id: 1, name_zh: '權杖' },
          { id: 2, name_zh: '聖杯' },
          { id: 3, name_zh: '寶劍' },
          { id: 4, name_zh: '錢幣' }
        ];
      }
    });
  }

  /** 自動抽牌：建立牌陣 → 隨機三張 → 依序紀錄 → 顯示 About My Day */
  autoDraw(): void {
    this.errorMessage = '';
    this.autoDrawing = true;
    this.spreadService.createSpreadReading().subscribe({
      next: (res: any) => {
        const id = res.data?.id ?? res.id;
        if (!id) {
          this.autoDrawing = false;
          this.errorMessage = '無法建立牌陣';
          return;
        }
        this.readingId = id;
        this.tarotCardService.getRandomCards(3).subscribe({
          next: (randRes: any) => {
            const cards: TarotCard[] = randRes.data ?? randRes ?? [];
            if (cards.length < 3) {
              this.autoDrawing = false;
              this.errorMessage = '隨機牌數不足';
              return;
            }
            this.addCardsThenRefresh(id, [
              { position: 1, card: cards[0], isReversed: Math.random() < 0.5 },
              { position: 2, card: cards[1], isReversed: Math.random() < 0.5 },
              { position: 3, card: cards[2], isReversed: Math.random() < 0.5 }
            ]);
          },
          error: () => {
            this.autoDrawing = false;
            this.errorMessage = '取得隨機牌失敗';
          }
        });
      },
      error: () => {
        this.autoDrawing = false;
        this.errorMessage = '建立牌陣失敗';
      }
    });
  }

  private addCardsThenRefresh(
    rid: number,
    items: Array<{ position: number; card: TarotCard; isReversed: boolean }>
  ): void {
    let done = 0;
    const total = items.length;
    const onFinish = (): void => {
      done++;
      if (done === total) {
        this.autoDrawing = false;
        this.refreshReading(rid);
      }
    };
    items.forEach(({ position, card, isReversed }) => {
      this.spreadService.addCard(rid, position, card.id, isReversed).subscribe({
        next: () => onFinish(),
        error: () => {
          this.autoDrawing = false;
          this.errorMessage = `紀錄第 ${position} 張牌失敗`;
        }
      });
    });
  }

  private refreshReading(rid: number): void {
    this.spreadService.getSpreadReading(rid).subscribe({
      next: (res: any) => this.applyReadingDetail(res.data ?? res),
      error: () => {
        this.errorMessage = '無法載入牌陣詳情';
      }
    });
  }

  /** 點選某格：進入手動抽牌（若尚無牌陣則先建立） */
  onSlotClick(position: number): void {
    const pos = position as 1 | 2 | 3;
    if (pos !== 1 && pos !== 2 && pos !== 3) return;
    if (this.slots[pos - 1].card) return;
    this.errorMessage = '';
    if (this.readingId == null) {
      this.autoDrawing = true;
      this.spreadService.createSpreadReading().subscribe({
        next: (res: any) => {
          const id = res.data?.id ?? res.id;
          this.readingId = id ?? null;
          this.autoDrawing = false;
          this.manualSlot = pos;
          this.manualStep = 'suit';
          this.pendingCard = null;
          this.cardsForPick = [];
        },
        error: () => {
          this.autoDrawing = false;
          this.errorMessage = '建立牌陣失敗';
        }
      });
    } else {
      this.manualSlot = pos;
      this.manualStep = 'suit';
      this.pendingCard = null;
      this.cardsForPick = [];
    }
  }

  /** 選擇花色（大牌 or 某個 suit） */
  selectSuit(suitId: number | null): void {
    this.loadingCards = true;
    this.cardsForPick = [];
    const params: { card_type?: 'major' | 'minor'; suit_id?: number; per_page: number } =
      suitId == null
        ? { card_type: 'major', per_page: 100 }
        : { suit_id: suitId, per_page: 100 };
    this.tarotCardService.getAllCards(params).subscribe({
      next: (res: any) => {
        const data = res.data ?? res;
        this.cardsForPick = Array.isArray(data) ? data : (data?.data ?? []);
        this.manualStep = 'card';
        this.loadingCards = false;
      },
      error: () => {
        this.loadingCards = false;
        this.errorMessage = '載入牌組失敗';
      }
    });
  }

  /** 手動選牌：選定一張牌後進入正/逆位選擇 */
  selectCard(card: TarotCard): void {
    if (this.manualSlot == null) return;
    this.pendingCard = card;
    this.manualStep = 'orientation';
  }

  /** 手動選牌：確認以正位或逆位紀錄 */
  confirmCardOrientation(isReversed: boolean): void {
    if (this.readingId == null || this.manualSlot == null || this.pendingCard == null) return;
    this.errorMessage = '';
    this.spreadService.addCard(this.readingId, this.manualSlot, this.pendingCard.id, isReversed).subscribe({
      next: () => {
        this.manualSlot = null;
        this.manualStep = 'suit';
        this.pendingCard = null;
        this.cardsForPick = [];
        this.refreshReading(this.readingId!);
      },
      error: () => {
        this.errorMessage = '紀錄此張牌失敗';
      }
    });
  }

  closeManualPick(): void {
    this.manualSlot = null;
    this.manualStep = 'suit';
    this.pendingCard = null;
    this.cardsForPick = [];
  }

  getCardImageUrl(card: TarotCard, isReversed = false): string {
    return this.tarotCardService.getCardImageUrl(card, isReversed)
      || this.tarotCardService.getCardImagePath(card.id);
  }

  getActiveTags(card: TarotCard): Array<{ id: number; name_zh: string; color?: string | null }> {
    if (!card?.tags) return [];
    const t = card.tags as { active?: unknown[]; default?: unknown[] };
    const list = (t.active && t.active.length > 0 ? t.active : t.default) ?? [];
    return (Array.isArray(list) ? list : []).map((item: Record<string, unknown>) => ({
      id: (item.id as number) ?? 0,
      name_zh: (item.name_zh as string) || (item.name as string) || '',
      color: (item.color as string) ?? null
    })).filter(tag => tag.name_zh && tag.id);
  }

  isTagSelected(slot: SlotCard, tagId: number): boolean {
    return (slot.selectedTagIds || []).indexOf(tagId) !== -1;
  }

  toggleTag(slot: SlotCard, tagId: number): void {
    if (this.readingId == null) return;
    this.spreadService.toggleSpreadCardTag(this.readingId, slot.position_number, tagId).subscribe({
      next: (res: any) => {
        const data = res.data ?? res;
        slot.selectedTagIds = data.selected_tag_ids ?? slot.selectedTagIds;
      },
      error: () => {
        this.errorMessage = '更新標籤狀態失敗';
      }
    });
  }
}
