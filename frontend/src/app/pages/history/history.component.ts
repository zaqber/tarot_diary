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

  /** 檢視月份 YYYY-MM（台北日曆） */
  viewMonthKey: string;
  /** 主題篩選：空字串＝全部 */
  filterTheme = '';
  /** 問題敘述篩選 */
  filterQuestion: 'all' | 'yes' | 'no' = 'all';
  /** 該月列表 API 分頁 */
  currentPage = 1;
  listMeta: { current_page: number; last_page: number; per_page: number; total: number } | null =
    null;

  readonly themeFilterOptions: Array<{ key: string; label: string }> = [
    { key: '', label: '全部主題' },
    { key: 'overall', label: '整體' },
    { key: 'love', label: '感情' },
    { key: 'career', label: '事業' },
    { key: 'finance', label: '財務' }
  ];

  /** 主題分類標籤色（對應 API `theme`：overall | love | career | finance） */
  themeBadgeClass(theme?: string | null): string {
    const t = (theme || '').trim().toLowerCase();
    const map: Record<string, string> = {
      overall: 'history-theme--overall',
      love: 'history-theme--love',
      career: 'history-theme--career',
      finance: 'history-theme--finance'
    };
    return map[t] ?? 'history-theme--unknown';
  }

  constructor(
    private spreadService: SpreadService,
    private tarotCardService: TarotCardService
  ) {
    this.viewMonthKey = this.getCurrentMonthKey();
  }

  ngOnInit(): void {
    this.loadList();
  }

  getCurrentMonthKey(): string {
    return getTodayDateStringInTaipei().slice(0, 7);
  }

  get viewMonthLabel(): string {
    const [y, m] = this.viewMonthKey.split('-').map(Number);
    return `${y} 年 ${m} 月`;
  }

  get isViewingCurrentMonth(): boolean {
    return this.viewMonthKey === this.getCurrentMonthKey();
  }

  get canGoNextMonth(): boolean {
    return this.viewMonthKey < this.getCurrentMonthKey();
  }

  get hasNonDefaultFilters(): boolean {
    return this.filterTheme !== '' || this.filterQuestion !== 'all';
  }

  goPrevMonth(): void {
    this.viewMonthKey = this.addMonthsYm(this.viewMonthKey, -1);
    this.currentPage = 1;
    this.loadList();
  }

  goNextMonth(): void {
    if (!this.canGoNextMonth) {
      return;
    }
    this.viewMonthKey = this.addMonthsYm(this.viewMonthKey, 1);
    this.currentPage = 1;
    this.loadList();
  }

  onFilterChange(): void {
    this.currentPage = 1;
    this.loadList();
  }

  resetFilters(): void {
    if (!this.hasNonDefaultFilters) {
      return;
    }
    this.filterTheme = '';
    this.filterQuestion = 'all';
    this.currentPage = 1;
    this.loadList();
  }

  goListPage(p: number): void {
    if (!this.listMeta || p < 1 || p > this.listMeta.last_page) {
      return;
    }
    this.currentPage = p;
    this.loadList();
    if (typeof window !== 'undefined') {
      window.scrollTo(0, 0);
    }
  }

  private addMonthsYm(ym: string, delta: number): string {
    const [y, mo] = ym.split('-').map(Number);
    const d = new Date(Date.UTC(y, mo - 1 + delta, 1));
    return `${d.getUTCFullYear()}-${String(d.getUTCMonth() + 1).padStart(2, '0')}`;
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

  private buildListQuery(): {
    per_page: number;
    page: number;
    month: string;
    theme?: string;
    has_question?: boolean;
  } {
    const q: {
      per_page: number;
      page: number;
      month: string;
      theme?: string;
      has_question?: boolean;
    } = {
      per_page: 200,
      page: this.currentPage,
      month: this.viewMonthKey
    };
    if (this.filterTheme) {
      q.theme = this.filterTheme;
    }
    if (this.filterQuestion === 'yes') {
      q.has_question = true;
    } else if (this.filterQuestion === 'no') {
      q.has_question = false;
    }
    return q;
  }

  private loadList(): void {
    this.loading = true;
    this.errorMessage = '';
    this.spreadService.getReadingList(this.buildListQuery()).subscribe({
      next: (res: any) => {
        const raw = res.data ?? res ?? [];
        const arr = Array.isArray(raw) ? raw : [];
        this.list = arr.filter((item: SpreadReadingListItem) =>
          hasReadableSpreadCards(item.spread_cards)
        );
        const m = res.meta;
        this.listMeta =
          m && typeof m.current_page === 'number'
            ? {
                current_page: m.current_page,
                last_page: m.last_page ?? 1,
                per_page: m.per_page ?? 200,
                total: m.total ?? this.list.length
              }
            : null;
        this.todayCarouselIndex = 0;
        this.loading = false;
      },
      error: () => {
        this.errorMessage = '無法載入紀錄';
        this.listMeta = null;
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
