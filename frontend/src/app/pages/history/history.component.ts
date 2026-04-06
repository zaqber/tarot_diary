import { Component, OnInit } from '@angular/core';
import { SpreadService, SpreadReadingListItem } from '../../services/spread.service';
import { TarotCardService } from '../../services/tarot-card.service';
import { getTodayDateStringInTaipei, formatDateDisplay } from '../../services/date.util';
import { hasReadableSpreadCards } from '../../utils/spread-reading.util';

export interface HistoryDateGroup {
  dateKey: string;
  items: SpreadReadingListItem[];
}

@Component({
  selector: 'app-history',
  templateUrl: './history.component.html',
  styleUrls: ['./history.component.css']
})
export class HistoryComponent implements OnInit {
  list: SpreadReadingListItem[] = [];
  loading = true;
  errorMessage = '';
  /** 僅「今天有多筆」時使用：目前顯示第幾筆（0＝今天內最新） */
  todayCarouselIndex = 0;

  constructor(
    private spreadService: SpreadService,
    private tarotCardService: TarotCardService
  ) {}

  ngOnInit(): void {
    this.loadList();
  }

  private getTodayDateString(): string {
    return getTodayDateStringInTaipei();
  }

  /** 今日所有紀錄（新→舊） */
  get todayReadings(): SpreadReadingListItem[] {
    const today = this.getTodayDateString();
    return this.list
      .filter(item => item.reading_date === today)
      .sort((a, b) => {
        const ta = a.reading_time ? new Date(a.reading_time).getTime() : 0;
        const tb = b.reading_time ? new Date(b.reading_time).getTime() : 0;
        if (tb !== ta) {
          return tb - ta;
        }
        return b.id - a.id;
      });
  }

  /** 今天以外的紀錄：依 reading_date 分組，日期新→舊，組內新→舊 */
  get pastDateGroups(): HistoryDateGroup[] {
    const today = this.getTodayDateString();
    const byDate = new Map<string, SpreadReadingListItem[]>();
    for (const item of this.list) {
      const dk = (item.reading_date || '').trim();
      if (!dk || dk === today) {
        continue;
      }
      if (!byDate.has(dk)) {
        byDate.set(dk, []);
      }
      byDate.get(dk)!.push(item);
    }
    const keys = [...byDate.keys()].sort((a, b) => b.localeCompare(a));
    return keys.map(dateKey => ({
      dateKey,
      items: this.sortItemsNewestFirst(byDate.get(dateKey)!)
    }));
  }

  private sortItemsNewestFirst(items: SpreadReadingListItem[]): SpreadReadingListItem[] {
    return [...items].sort((a, b) => {
      const ta = a.reading_time ? new Date(a.reading_time).getTime() : 0;
      const tb = b.reading_time ? new Date(b.reading_time).getTime() : 0;
      if (tb !== ta) {
        return tb - ta;
      }
      return b.id - a.id;
    });
  }

  /** 區塊標題：昨天／YYYY/MM/DD */
  groupDateHeading(dateKey: string): string {
    if (dateKey === this.getYesterdayDateString()) {
      return '昨天';
    }
    return formatDateDisplay(dateKey);
  }

  private getYesterdayDateString(): string {
    return this.addDaysToYmd(this.getTodayDateString(), -1);
  }

  /** 以 YYYY-MM-DD 做純日曆加減（與台北「今天」字串對齊，不依瀏覽器本地時區） */
  private addDaysToYmd(ymd: string, delta: number): string {
    const [y, m, d] = ymd.split('-').map(Number);
    const t = Date.UTC(y, m - 1, d + delta);
    const nd = new Date(t);
    return `${nd.getUTCFullYear()}-${String(nd.getUTCMonth() + 1).padStart(2, '0')}-${String(
      nd.getUTCDate()
    ).padStart(2, '0')}`;
  }

  /** 分組內卡片副標：有紀錄時間則顯示時分，否則顯示日期 */
  formatPastItemCaption(item: SpreadReadingListItem): string {
    if (item.reading_time) {
      try {
        return new Date(item.reading_time).toLocaleTimeString('zh-TW', {
          timeZone: 'Asia/Taipei',
          hour: '2-digit',
          minute: '2-digit',
          hour12: true
        });
      } catch {
        /* fall through */
      }
    }
    return formatDateDisplay(item.reading_date);
  }

  get currentTodaySlide(): SpreadReadingListItem | null {
    const arr = this.todayReadings;
    return arr[this.todayCarouselIndex] ?? null;
  }

  get canTodayGoNewer(): boolean {
    return this.todayCarouselIndex > 0;
  }

  get canTodayGoOlder(): boolean {
    const n = this.todayReadings.length;
    return n > 1 && this.todayCarouselIndex < n - 1;
  }

  todayGoNewer(): void {
    if (this.canTodayGoNewer) {
      this.todayCarouselIndex--;
    }
  }

  todayGoOlder(): void {
    if (this.canTodayGoOlder) {
      this.todayCarouselIndex++;
    }
  }

  private loadList(): void {
    this.loading = true;
    this.errorMessage = '';
    this.spreadService.getReadingList({ per_page: 80, page: 1 }).subscribe({
      next: (res: any) => {
        const raw = res.data ?? res ?? [];
        const arr = Array.isArray(raw) ? raw : [];
        this.list = arr.filter((item: SpreadReadingListItem) =>
          hasReadableSpreadCards(item.spread_cards)
        );
        this.todayCarouselIndex = 0;
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

  /** 今天多筆輪播：副標只顯示時間 */
  formatTodaySlideTime(item: SpreadReadingListItem): string {
    if (!item.reading_time) {
      return `第 ${this.todayCarouselIndex + 1} 筆`;
    }
    try {
      const d = new Date(item.reading_time);
      return d.toLocaleTimeString('zh-TW', {
        timeZone: 'Asia/Taipei',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
      });
    } catch {
      return `第 ${this.todayCarouselIndex + 1} 筆`;
    }
  }

  getOrderedCards(spreadCards: SpreadReadingListItem['spread_cards']): Array<{
    card_id: number;
    is_reversed?: boolean;
    card: { id: number; name: string; name_zh: string } | null;
  }> {
    if (!spreadCards?.length) return [];
    return [...spreadCards].sort((a, b) => a.position_number - b.position_number);
  }

  getCardImagePath(cardId: number): string {
    return this.tarotCardService.getCardImagePath(cardId);
  }
}
