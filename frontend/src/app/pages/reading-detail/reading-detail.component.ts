import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { SpreadService } from '../../services/spread.service';
import { TarotCardService } from '../../services/tarot-card.service';
import { TarotCard } from '../../models/tarot-card.model';
import { SpreadReadingDetail } from '../../models/spread-reading.model';
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

  constructor(
    private route: ActivatedRoute,
    private router: Router,
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
      next: (res: any) => this.applyReadingDetail(res.data ?? res),
      error: () => {
        this.loading = false;
        this.errorMessage = '無法載入此筆抽牌紀錄';
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

  goBack(): void {
    this.router.navigate(['/history']);
  }
}
