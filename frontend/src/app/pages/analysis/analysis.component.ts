import { Component, OnInit } from '@angular/core';
import {
  AnalysisDashboardResponse,
  AnalysisService,
  DistributionItem,
  KeywordTrendSeries,
  OrientationStats,
  SelectedCardRankingItem,
  TopCardStat
} from '../../services/analysis.service';
import { TarotCardService } from '../../services/tarot-card.service';

@Component({
  selector: 'app-analysis',
  templateUrl: './analysis.component.html',
  styleUrls: ['./analysis.component.css']
})
export class AnalysisComponent implements OnInit {
  days = 30;
  loading = true;
  errorMessage = '';
  orientation: OrientationStats = { upright_count: 0, reversed_count: 0, total: 0 };
  topCards: TopCardStat[] = [];
  distribution: DistributionItem[] = [];
  selectedCardRanking: SelectedCardRankingItem[] = [];
  trendLabels: string[] = [];
  trendSeries: KeywordTrendSeries[] = [];

  constructor(
    private analysisService: AnalysisService,
    private tarotCardService: TarotCardService
  ) {}

  ngOnInit(): void {
    this.loadDashboard();
  }

  loadDashboard(): void {
    this.loading = true;
    this.errorMessage = '';
    this.analysisService.getDashboard(this.days).subscribe({
      next: (res) => {
        this.applyDashboard(res.data);
        this.loading = false;
      },
      error: () => {
        this.errorMessage = '無法載入分析資料';
        this.loading = false;
      }
    });
  }

  private applyDashboard(data: AnalysisDashboardResponse): void {
    this.days = data?.days ?? 30;
    this.orientation = data?.orientation ?? { upright_count: 0, reversed_count: 0, total: 0 };
    this.topCards = data?.top_cards ?? [];
    this.distribution = data?.arcana_suit_distribution ?? [];
    this.selectedCardRanking = data?.selected_cards_ranking ?? [];
    this.trendLabels = data?.keyword_trend?.date_labels ?? [];
    this.trendSeries = data?.keyword_trend?.series ?? [];
  }

  getCardImagePath(cardId: number): string {
    return this.tarotCardService.getCardImagePath(cardId);
  }

  pieStyleFromItems(items: Array<{ count: number; color?: string | null }>): string {
    const total = items.reduce((sum, it) => sum + (it.count || 0), 0);
    if (total <= 0) {
      return 'conic-gradient(#d9e2ec 0 100%)';
    }
    let start = 0;
    const parts = items.map(it => {
      const ratio = (it.count || 0) / total;
      const end = start + ratio * 100;
      const seg = `${it.color || '#999'} ${start.toFixed(2)}% ${end.toFixed(2)}%`;
      start = end;
      return seg;
    });
    return `conic-gradient(${parts.join(', ')})`;
  }

  orientationPieStyle(): string {
    return this.pieStyleFromItems([
      { count: this.orientation.upright_count, color: '#2ecc71' },
      { count: this.orientation.reversed_count, color: '#e74c3c' }
    ]);
  }

  distributionPieStyle(): string {
    return this.pieStyleFromItems(this.distribution);
  }

  trendWindowLabels(): string[] {
    const windowSize = 14;
    const arr = this.trendLabels || [];
    return arr.length > windowSize ? arr.slice(arr.length - windowSize) : arr;
  }

  trendWindowData(series: KeywordTrendSeries): number[] {
    const windowSize = 14;
    const arr = series?.data || [];
    return arr.length > windowSize ? arr.slice(arr.length - windowSize) : arr;
  }

  trendMaxInWindow(series: KeywordTrendSeries): number {
    const data = this.trendWindowData(series);
    const max = data.reduce((m, n) => Math.max(m, n), 0);
    return max > 0 ? max : 1;
  }

  trendBarHeight(value: number, series: KeywordTrendSeries): string {
    const max = this.trendMaxInWindow(series);
    const pct = (value / max) * 100;
    return `${Math.max(8, pct)}%`;
  }

  formatTrendDate(isoDate: string): string {
    const parts = isoDate.split('-');
    if (parts.length !== 3) {
      return isoDate;
    }
    return `${parts[1]}/${parts[2]}`;
  }
}
