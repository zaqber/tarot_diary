import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { SpreadService } from '../../services/spread.service';
import { TarotCardService } from '../../services/tarot-card.service';
import { TarotCard } from '../../models/tarot-card.model';
import { SpreadReadingDetail } from '../../models/spread-reading.model';
import { hasReadableSpreadCards } from '../../utils/spread-reading.util';
interface SlotCard {
  position_number: number;
  card: TarotCard | null;
  isReversed: boolean;
  selectedTagIds: number[];
}

@Component({
  selector: 'app-reading-detail',
  templateUrl: './reading-detail.component.html',
  styleUrls: ['./reading-detail.component.css']
})
export class ReadingDetailComponent implements OnInit {
  readingId: number | null = null;
  readingDetail: SpreadReadingDetail | null = null;
  slots: SlotCard[] = [
    { position_number: 1, card: null, isReversed: false, selectedTagIds: [] },
    { position_number: 2, card: null, isReversed: false, selectedTagIds: [] },
    { position_number: 3, card: null, isReversed: false, selectedTagIds: [] }
  ];
  loading = true;
  errorMessage = '';
  aiQuestionDraft = '';
  aiInterpretLoading = false;
  aiError = '';

  constructor(
    private route: ActivatedRoute,
    private spreadService: SpreadService,
    private tarotCardService: TarotCardService
  ) {}

  ngOnInit(): void {
    this.route.params.subscribe(params => {
      const id = params['id'];
      if (id) {
        this.readingId = +id;
        if (!isNaN(this.readingId)) {
          this.loadReading();
        } else {
          this.loading = false;
          this.errorMessage = '無效的紀錄 ID';
        }
      } else {
        this.loading = false;
        this.errorMessage = '缺少紀錄 ID';
      }
    });
  }

  private loadReading(): void {
    this.loading = true;
    this.errorMessage = '';
    this.spreadService.getSpreadReading(this.readingId!).subscribe({
      next: (res: any) => {
        const detail = res?.data ?? res;
        if (!detail || typeof detail !== 'object' || detail.id == null) {
          this.loading = false;
          this.readingDetail = null;
          this.errorMessage = '伺服器回傳資料異常，請重新整理或從 History 再開啟。';
          return;
        }
        this.applyReadingDetail(detail);
      },
      error: (err: { status?: number }) => {
        this.loading = false;
        this.readingDetail = null;
        if (err?.status === 404) {
          this.errorMessage =
            '找不到這筆紀錄，或該紀錄不屬於目前登入帳號（請確認是否用同一使用者登入）。';
        } else {
          this.errorMessage = '無法載入此筆抽牌紀錄，請確認已登入且後端正常。';
        }
      }
    });
  }

  private applyReadingDetail(detail: SpreadReadingDetail): void {
    this.readingDetail = detail;
    this.loading = false;
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

  get allSlotsFilled(): boolean {
    return this.slots.every(s => s.card != null);
  }

  /** 至少已有一張牌（用於區分「完全沒抽牌」的紀錄） */
  get hasAnyCard(): boolean {
    return this.slots.some(s => s.card != null);
  }

  /** API 的 spread_cards 是否至少有一張對得到 tarot_cards */
  get hasReadableSpread(): boolean {
    return hasReadableSpreadCards(this.readingDetail?.spread_cards);
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
    this.aiError = '';
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
        this.aiError =
          typeof msg === 'string' ? msg : 'AI 解牌失敗，請確認後端已設定 API Key';
      }
    });
  }

  get displayDate(): string {
    const dateStr = this.readingDetail?.reading_date;
    if (!dateStr) return '';
    const part = dateStr.trim().split('T')[0].split('-');
    if (part.length !== 3) return dateStr;
    const [y, m, d] = part;
    return `${y}年${parseInt(m, 10)}月${parseInt(d, 10)}日`;
  }

  getCardImageUrl(card: TarotCard, _isReversed = false): string {
    return this.tarotCardService.getCardImageUrl(card, false)
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

  /** 同一牌陣中出現超過一張牌的關鍵字，才顯示該標籤的 color */
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

  isTagNameRepeated(nameZh: string): boolean {
    return this.getRepeatedTagNames().has((nameZh || '').trim());
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
