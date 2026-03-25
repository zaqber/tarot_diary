import { Component, OnDestroy, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { forkJoin, of } from 'rxjs';
import { map, switchMap } from 'rxjs/operators';
import { SpreadService } from '../../services/spread.service';
import { TarotCardService } from '../../services/tarot-card.service';
import { SuitService } from '../../services/suit.service';
import { TarotCard } from '../../models/tarot-card.model';
import { SpreadReadingDetail } from '../../models/spread-reading.model';
import { Suit } from '../../services/suit.service';
import { getTodayDateStringInTaipei } from '../../services/date.util';

interface SlotCard {
  position_number: number;
  card: TarotCard | null;
  /** 是否為逆位 */
  isReversed: boolean;
  /** 符合當天狀態的 tag id 列表（點選後由後端紀錄） */
  selectedTagIds: number[];
}

/**
 * 洗牌儀式畫面：參考實體牌「散亂堆在桌布上」——由中心向外多層密度、隨機角度與疊放（僅視覺）
 */
interface ShuffleVisualSlot {
  dx: number;
  dy: number;
  tilt: number;
  z: number;
  w: number;
  h: number;
  jx1: number;
  jy1: number;
  jx2: number;
  jy2: number;
  jx3: number;
  jy3: number;
  swing1: number;
  swing2: number;
  swing3: number;
}

@Component({
  selector: 'app-new-spread',
  templateUrl: './new-spread.component.html',
  styleUrls: ['./new-spread.component.css']
})
export class NewSpreadComponent implements OnInit, OnDestroy {
  readingId: number | null = null;
  slots: SlotCard[] = [
    { position_number: 1, card: null, isReversed: false, selectedTagIds: [] },
    { position_number: 2, card: null, isReversed: false, selectedTagIds: [] },
    { position_number: 3, card: null, isReversed: false, selectedTagIds: [] }
  ];
  readingDetail: SpreadReadingDetail | null = null;

  /** 自動抽牌中（含儀式與 API 紀錄） */
  autoDrawing = false;

  /**
   * 自動抽牌儀式階段：idle 關閉覆蓋層；shuffle→cut→arrange→pick→submitting
   */
  autoRitualPhase: 'idle' | 'shuffle' | 'cut' | 'arrange' | 'pick' | 'submitting' = 'idle';
  /** 儀式用：洗牌後取前 N 張背面朝上供選 */
  ritualDeck: TarotCard[] = [];
  /** 使用者已選的牌（依序對應第 1～3 張位置） */
  ritualSelected: TarotCard[] = [];
  private shuffleEndTimer: ReturnType<typeof setTimeout> | null = null;
  private shuffleStartTimer: ReturnType<typeof setTimeout> | null = null;
  /** 進入洗牌步驟時先短暫停住，之後才開始洗 */ 
  shuffleAnimationStarted = false;
  /** 切牌動畫是否已觸發 */
  cutDeckAnimated = false;
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

  /** 給 AI 的選填提問 */
  aiQuestionDraft = '';
  /** 請求 AI 解牌中 */
  aiInterpretLoading = false;

  /** 抽牌主題（建立牌陣時寫入 DB） */
  readonly themeOptions: Array<{ key: string; label: string }> = [
    { key: 'overall', label: '整體' },
    { key: 'love', label: '感情' },
    { key: 'career', label: '事業' },
    { key: 'finance', label: '財務' }
  ];
  selectedTheme = 'overall';

  /** 洗牌動畫用牌位：約 32 張，散亂堆疊（與參考圖類似） */
  readonly shuffleVisualSlots: ShuffleVisualSlot[] = NewSpreadComponent.buildMessyShuffleSlots();

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

  ngOnDestroy(): void {
    this.clearRitualTimers();
  }

  /** 主按鈕：儀式進行中 */
  get autoRitualInProgress(): boolean {
    return this.autoRitualPhase !== 'idle' && this.autoRitualPhase !== 'submitting';
  }

  get ritualPickStepLabel(): string {
    const n = this.ritualSelected.length + 1;
    if (n > 3) return '已完成';
    return `第 ${n} 張`;
  }

  getRitualCardBackUrl(): string {
    return this.tarotCardService.getCoverImagePath();
  }

  /** 每張牌略不同的週期與相位，洗牌感較自然 */
  shuffleCardAnim(idx: number): { duration: string; delay: string } {
    const d = 5.4 + (idx % 19) * 0.24;
    const del = -((idx * 0.2) % 4.8);
    return { duration: `${d.toFixed(2)}s`, delay: `${del.toFixed(2)}s` };
  }

  trackByShuffleIdx(_i: number, _s: ShuffleVisualSlot): number {
    return _i;
  }

  /** 0–1 決定性雜湊（同 index 每次畫面一致） */
  private static shuffleHash01(n: number): number {
    const x = Math.sin(n * 127.1 + 311.7) * 43758.5453123;
    return x - Math.floor(x);
  }

  /**
   * 內圈密集核心 + 中圈 + 外圈較散，角度與位移隨機，z 交錯疊放
   */
  private static buildMessyShuffleSlots(): ShuffleVisualSlot[] {
    const out: ShuffleVisualSlot[] = [];
    const count = 46;
    const baseW = 46;
    const baseH = 70;

    for (let i = 0; i < count; i++) {
      const u = NewSpreadComponent.shuffleHash01(i);
      const v = NewSpreadComponent.shuffleHash01(i + 17);
      const w = NewSpreadComponent.shuffleHash01(i + 29);
      const jx = NewSpreadComponent.shuffleHash01(i + 41);
      const jy = NewSpreadComponent.shuffleHash01(i + 53);

      let minR: number;
      let maxR: number;
      /* 半徑略縮、抖動略减 → 整體更密集 */
      if (u < 0.42) {
        minR = 0;
        maxR = 38;
      } else if (u < 0.72) {
        minR = 20;
        maxR = 86;
      } else {
        minR = 62;
        maxR = 128;
      }

      const radius = minR + v * (maxR - minR);
      const theta = w * Math.PI * 2;
      let dx = Math.cos(theta) * radius;
      let dy = Math.sin(theta) * radius;
      dx += (jx - 0.5) * 28;
      dy += (jy - 0.5) * 28;

      const tilt = NewSpreadComponent.shuffleHash01(i + 61) * 360;
      const z = 3 + Math.floor(NewSpreadComponent.shuffleHash01(i + 73) * 52);
      const sc = 0.72 + NewSpreadComponent.shuffleHash01(i + 89) * 0.38;
      const wPx = Math.round(baseW * sc);
      const hPx = Math.round(baseH * sc);

      const jx1 = (NewSpreadComponent.shuffleHash01(i + 101) - 0.5) * 16;
      const jy1 = (NewSpreadComponent.shuffleHash01(i + 113) - 0.5) * 16;
      const jx2 = (NewSpreadComponent.shuffleHash01(i + 127) - 0.5) * 14;
      const jy2 = (NewSpreadComponent.shuffleHash01(i + 139) - 0.5) * 14;
      const jx3 = (NewSpreadComponent.shuffleHash01(i + 171) - 0.5) * 20;
      const jy3 = (NewSpreadComponent.shuffleHash01(i + 181) - 0.5) * 20;
      const swing1 = 1 + NewSpreadComponent.shuffleHash01(i + 151) * 2.6;
      const swing2 = 0.8 + NewSpreadComponent.shuffleHash01(i + 163) * 2.2;
      const swing3 = 1.2 + NewSpreadComponent.shuffleHash01(i + 193) * 3.1;

      out.push({
        dx,
        dy,
        tilt,
        z,
        w: wPx,
        h: hPx,
        jx1,
        jy1,
        jx2,
        jy2,
        jx3,
        jy3,
        swing1,
        swing2,
        swing3
      });
    }

    return out;
  }

  /**
   * 進入頁面時：僅在有 query readingId 時載入該筆。
   * 預設為空白新牌陣（同一天可多次抽牌，不再自動載入「今日唯一一筆」）。
   */
  private loadInitialReading(): void {
    const readingId = this.route.snapshot.queryParamMap.get('readingId');
    if (!readingId) {
      return;
    }
    const id = +readingId;
    if (isNaN(id)) {
      return;
    }
    this.spreadService.getSpreadReading(id).subscribe({
      next: (res: any) => this.applyReadingDetail(res.data ?? res),
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
    if (detail.theme) {
      this.selectedTheme = detail.theme;
    }
    this.slots = [1, 2, 3].map(pos => {
      const sc = detail.spread_cards?.find(c => c.position_number === pos);
      return {
        position_number: pos,
        card: sc?.card ?? null,
        isReversed: sc?.is_reversed ?? false,
        selectedTagIds: sc?.selected_tag_ids ?? []
      };
    });
    if (detail.ai_interpretation) {
      this.aiQuestionDraft = detail.ai_question || '';
    } else if (detail.ai_question) {
      this.aiQuestionDraft = detail.ai_question;
    }
  }

  formatAiTime(iso: string | null | undefined): string {
    if (!iso) return '';
    try {
      return new Date(iso).toLocaleString('zh-TW', { dateStyle: 'medium', timeStyle: 'short' });
    } catch {
      return iso;
    }
  }

  requestAiInterpret(): void {
    if (this.readingId == null || !this.allSlotsFilled) return;
    this.errorMessage = '';
    this.aiInterpretLoading = true;
    this.spreadService.requestAiInterpret(
      this.readingId,
      this.aiQuestionDraft || undefined
    ).subscribe({
      next: (res: any) => {
        this.aiInterpretLoading = false;
        this.applyReadingDetail(res.data ?? res);
      },
      error: (err: { error?: { message?: string } }) => {
        this.aiInterpretLoading = false;
        const msg = err.error?.message;
        this.errorMessage =
          typeof msg === 'string' ? msg : 'AI 解牌失敗，請確認後端已設定 GEMINI_API_KEY（與 AI_PROVIDER）';
      }
    });
  }

  get allSlotsFilled(): boolean {
    return this.slots.every(s => s.card != null);
  }

  get showAboutMyDay(): boolean {
    return this.allSlotsFilled && this.readingDetail != null;
  }

  get hasAnyCardDrawn(): boolean {
    return this.slots.some(s => s.card != null);
  }

  /**
   * 頂部顯示牌陣摘要：目前這筆紀錄至少已抽一張時（不限是否「今日」）
   */
  get showTopAboutMyDay(): boolean {
    return this.readingId != null && this.readingDetail != null && this.hasAnyCardDrawn;
  }

  /** 繼續抽牌區標題 */
  get continueSectionTitle(): string {
    return this.showTopAboutMyDay && !this.allSlotsFilled ? '繼續完成此筆牌陣' : 'Start My Day';
  }

  /** 已抽至少一張後鎖定主題；僅建立紀錄尚未抽牌時仍可改 */
  get themeLocked(): boolean {
    return this.hasAnyCardDrawn;
  }

  selectTheme(key: string): void {
    if (this.themeLocked) return;
    this.selectedTheme = key;
    if (this.readingId != null) {
      this.spreadService.updateReadingTheme(this.readingId, key).subscribe({
        next: (res: any) => {
          const d = res.data ?? res;
          if (this.readingDetail && d?.theme) {
            this.readingDetail = {
              ...this.readingDetail,
              theme: d.theme,
              theme_label_zh: d.theme_label_zh
            };
          }
        },
        error: () => {
          this.errorMessage = '無法更新主題，請稍後再試';
        }
      });
    }
  }

  get themeLabel(): string {
    return this.themeOptions.find(t => t.key === this.selectedTheme)?.label ?? '整體';
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

  /**
   * 自動抽牌：建立牌陣 → 儀式（洗牌 → 切牌 → 展牌 → 自選三張）→ 紀錄
   */
  autoDraw(): void {
    if (this.hasAnyCardDrawn) {
      this.errorMessage =
        '此筆牌陣已開始抽牌，請用手動方式補滿空位。若要全新再抽一組，請點「開始全新牌陣」或選單的 New Spread。';
      return;
    }
    this.errorMessage = '';
    this.autoDrawing = true;
    this.autoRitualPhase = 'shuffle';
    this.ritualSelected = [];
    this.ritualDeck = [];
    this.cutDeckAnimated = false;
    this.clearRitualTimers();

    this.spreadService.createSpreadReading(this.selectedTheme).subscribe({
      next: (res: any) => {
        const id = res.data?.id ?? res.id;
        if (!id) {
          this.resetAutoRitualOnError('無法建立牌陣');
          return;
        }
        this.readingId = id;
        this.fetchAllTarotCards$().subscribe({
          next: all => {
            if (all.length < 3) {
              this.resetAutoRitualOnError('牌組資料不足，無法進行儀式');
              return;
            }
            const shuffled = this.shuffleArray(all);
            // 可選牌池控制在較多但不易超出版面的張數
            const fanSize = Math.min(28, shuffled.length);
            this.ritualDeck = shuffled.slice(0, fanSize);
            this.scheduleShuffleEnd();
          },
          error: () => {
            this.resetAutoRitualOnError('載入牌組失敗');
          }
        });
      },
      error: () => {
        this.resetAutoRitualOnError('建立牌陣失敗');
      }
    });
  }

  /** 載入全部塔羅牌（含分頁）供儀式洗牌用 */
  private fetchAllTarotCards$() {
    return this.tarotCardService.getAllCards({ per_page: 100, page: 1 }).pipe(
      switchMap((res: any) => {
        const first = (res.data ?? []) as TarotCard[];
        const meta = res.meta;
        const lastPage = meta?.last_page ?? 1;
        if (lastPage <= 1) {
          return of(first);
        }
        const pageCalls = [];
        for (let p = 2; p <= lastPage; p++) {
          pageCalls.push(this.tarotCardService.getAllCards({ per_page: 100, page: p }));
        }
        return forkJoin(pageCalls).pipe(
          map((pages: any[]) => {
            let acc = [...first];
            pages.forEach(r => {
              acc = acc.concat((r.data ?? []) as TarotCard[]);
            });
            return acc;
          })
        );
      })
    );
  }

  private shuffleArray<T>(arr: T[]): T[] {
    const a = [...arr];
    for (let i = a.length - 1; i > 0; i--) {
      const j = Math.floor(Math.random() * (i + 1));
      [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
  }

  private clearRitualTimers(): void {
    if (this.shuffleStartTimer != null) {
      clearTimeout(this.shuffleStartTimer);
      this.shuffleStartTimer = null;
    }
    if (this.shuffleEndTimer != null) {
      clearTimeout(this.shuffleEndTimer);
      this.shuffleEndTimer = null;
    }
  }

  private scheduleShuffleEnd(): void {
    this.clearRitualTimers();
    this.shuffleAnimationStarted = false;
    this.shuffleStartTimer = setTimeout(() => {
      this.shuffleStartTimer = null;
      if (this.autoRitualPhase === 'shuffle') {
        this.shuffleAnimationStarted = true;
      }
    }, 500);
    const minMs = 7000;
    this.shuffleEndTimer = setTimeout(() => {
      this.shuffleEndTimer = null;
      if (this.autoRitualPhase !== 'shuffle') {
        return;
      }
      this.autoRitualPhase = 'cut';
    }, minMs);
  }

  private resetAutoRitualOnError(msg: string): void {
    this.clearRitualTimers();
    this.autoDrawing = false;
    this.autoRitualPhase = 'idle';
    this.shuffleAnimationStarted = false;
    this.ritualDeck = [];
    this.ritualSelected = [];
    this.cutDeckAnimated = false;
    this.errorMessage = msg;
  }

  /** 取消儀式（保留已建立的牌陣，可改用手動抽牌） */
  cancelAutoRitual(): void {
    if (this.autoRitualPhase === 'idle' || this.autoRitualPhase === 'submitting') {
      return;
    }
    this.clearRitualTimers();
    this.autoRitualPhase = 'idle';
    this.autoDrawing = false;
    this.shuffleAnimationStarted = false;
    this.ritualDeck = [];
    this.ritualSelected = [];
    this.cutDeckAnimated = false;
  }

  /** 步驟 2：使用者點擊完成切牌 */
  onCutDeckClick(): void {
    if (this.autoRitualPhase !== 'cut' || this.cutDeckAnimated) {
      return;
    }
    this.cutDeckAnimated = true;
    setTimeout(() => {
      if (this.autoRitualPhase === 'cut') {
        this.autoRitualPhase = 'pick';
      }
      this.cutDeckAnimated = false;
    }, 600);
  }

  isRitualCardPicked(card: TarotCard): boolean {
    return this.ritualSelected.some(c => c.id === card.id);
  }

  /** 步驟 4：點選一張背面牌 */
  onRitualCardPick(card: TarotCard): void {
    if (this.autoRitualPhase !== 'pick') {
      return;
    }
    if (this.ritualSelected.length >= 3) {
      return;
    }
    if (this.isRitualCardPicked(card)) {
      return;
    }
    this.ritualSelected = [...this.ritualSelected, card];
    if (this.ritualSelected.length === 3) {
      const id = this.readingId;
      if (!id) {
        this.resetAutoRitualOnError('無法紀錄牌陣');
        return;
      }
      this.autoRitualPhase = 'submitting';
      const items = this.ritualSelected.map((c, i) => ({
        position: (i + 1) as 1 | 2 | 3,
        card: c,
        isReversed: Math.random() < 0.5
      }));
      this.addCardsThenRefresh(id, items);
    }
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
        this.autoRitualPhase = 'idle';
        this.ritualDeck = [];
        this.ritualSelected = [];
        this.refreshReading(rid);
      }
    };
    items.forEach(({ position, card, isReversed }) => {
      this.spreadService.addCard(rid, position, card.id, isReversed).subscribe({
        next: () => onFinish(),
        error: () => {
          this.autoDrawing = false;
          this.autoRitualPhase = 'idle';
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
      this.spreadService.createSpreadReading(this.selectedTheme).subscribe({
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

  /** 同一牌陣中出現超過一張牌的關鍵字（name_zh），這些標籤顯示黃褐色 */
  getRepeatedTagNames(): Set<string> {
    const countByName = new Map<string, number>();
    this.slots.forEach(slot => {
      if (!slot.card) return;
      this.getActiveTags(slot.card).forEach(tag => {
        const name = (tag.name_zh || '').trim();
        if (!name) return;
        countByName.set(name, (countByName.get(name) ?? 0) + 1);
      });
    });
    const repeated = new Set<string>();
    countByName.forEach((count, name) => {
      if (count > 1) repeated.add(name);
    });
    return repeated;
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
