import { Component, OnInit } from '@angular/core';
import { SpreadService, SpreadReadingListItem } from '../../services/spread.service';

@Component({
  selector: 'app-history',
  templateUrl: './history.component.html',
  styleUrls: ['./history.component.css']
})
export class HistoryComponent implements OnInit {
  list: SpreadReadingListItem[] = [];
  loading = true;
  errorMessage = '';

  constructor(private spreadService: SpreadService) {}

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
    if (!dateStr) return '—';
    const d = new Date(dateStr);
    return isNaN(d.getTime()) ? dateStr : `${d.getFullYear()}/${String(d.getMonth() + 1).padStart(2, '0')}/${String(d.getDate()).padStart(2, '0')}`;
  }
}
