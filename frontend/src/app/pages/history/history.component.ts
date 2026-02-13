import { Component, OnInit } from '@angular/core';
import { SpreadService, SpreadReadingListItem } from '../../services/spread.service';
import { TarotCardService } from '../../services/tarot-card.service';
import { getTodayDateStringInTaipei, formatDateDisplay } from '../../utils/date.util';

@Component({
  selector: 'app-history',
  templateUrl: './history.component.html',
  styleUrls: ['./history.component.css']
})
export class HistoryComponent implements OnInit {
  list: SpreadReadingListItem[] = [];
  loading = true;
  errorMessage = '';

  /** 今日的紀錄（若有）單獨顯示在最上面 */
  get todayReading(): SpreadReadingListItem | null {
    const today = this.getTodayDateString();
    return this.list.find(item => item.reading_date === today) ?? null;
  }

  /** 過往紀錄（排除今天），供下方網格顯示 */
  get pastList(): SpreadReadingListItem[] {
    const today = this.getTodayDateString();
    return this.list.filter(item => item.reading_date !== today);
  }

  private getTodayDateString(): string {
    return getTodayDateStringInTaipei();
  }

  constructor(
    private spreadService: SpreadService,
    private tarotCardService: TarotCardService
  ) {}

  ngOnInit(): void {
    this.loadList();
  }

  private loadList(): void {
    this.loading = true;
    this.errorMessage = '';
    this.spreadService.getReadingList({ per_page: 30, page: 1 }).subscribe({
      next: (res: any) => {
        this.list = res.data ?? res ?? [];
        this.loading = false;
      },
      error: () => {
        this.errorMessage = '無法載入紀錄';
        this.loading = false;
      }
    });
  }

  getCardNames(item: SpreadReadingListItem): string {
    const names = (item.spread_cards || [])
      .sort((a, b) => a.position_number - b.position_number)
      .map(sc => sc.card?.name_zh || sc.card?.name || '—');
    return names.join('、') || '—';
  }

  formatDate(dateStr: string): string {
    return formatDateDisplay(dateStr);
  }

  getOrderedCards(spreadCards: SpreadReadingListItem['spread_cards']): Array<{ card_id: number; card: { id: number; name: string; name_zh: string } | null }> {
    if (!spreadCards?.length) return [];
    return [...spreadCards].sort((a, b) => a.position_number - b.position_number);
  }

  getCardImagePath(cardId: number): string {
    return this.tarotCardService.getCardImagePath(cardId);
  }
}
