import { Component, OnInit } from '@angular/core';
import { AnalysisService, TopKeywordGroup } from '../../services/analysis.service';

/** 顯示用：左=第2名、中=第1名、右=第3名 */
export interface RankSlot {
  rank: number;
  group: TopKeywordGroup;
}

@Component({
  selector: 'app-analysis',
  templateUrl: './analysis.component.html',
  styleUrls: ['./analysis.component.css']
})
export class AnalysisComponent implements OnInit {
  /** 依序為 [第2名, 第1名, 第3名]，讓第一名在正中間 */
  displaySlots: RankSlot[] = [];
  topKeywordsDays = 30;
  loading = true;
  errorMessage = '';

  constructor(private analysisService: AnalysisService) {}

  ngOnInit(): void {
    this.loadTopKeywords();
  }

  loadTopKeywords(): void {
    this.loading = true;
    this.errorMessage = '';
    this.analysisService.getTopKeywords(this.topKeywordsDays).subscribe({
      next: (res) => {
        const data = res.data;
        const groups = data?.groups ?? [];
        this.topKeywordsDays = data?.days ?? 30;
        this.displaySlots = this.buildDisplaySlots(groups);
        this.loading = false;
      },
      error: () => {
        this.errorMessage = '無法載入 TOP3 關鍵字';
        this.loading = false;
      }
    });
  }

  /** 排列為 [2nd, 1st, 3rd]，第一名正中間 */
  private buildDisplaySlots(groups: TopKeywordGroup[]): RankSlot[] {
    const slots: RankSlot[] = [];
    const order = [2, 1, 3]; // 視覺順序：左2、中1、右3
    order.forEach((rank, i) => {
      if (groups[i]) {
        slots.push({ rank, group: groups[i] });
      }
    });
    return slots;
  }

  /** 同組關鍵字並排顯示，例如「行動、沈思」 */
  getGroupLabel(group: TopKeywordGroup): string {
    const items = Array.isArray(group) ? group : (group?.items || []);
    return items
      .map((item: { name_zh?: string; name?: string }) => item?.name_zh || item?.name || '')
      .filter(Boolean)
      .join('、');
  }

  /** 柱狀顏色：取同組第一個的 color */
  getGroupColor(group: TopKeywordGroup): string {
    const items = Array.isArray(group) ? group : (group?.items || []);
    const first = items[0];
    return (first?.color as string) || '#9370DB';
  }

  /** 同組次數（相容後端 { count, items } 或舊版純陣列） */
  getGroupCount(group: TopKeywordGroup): number {
    if (group && typeof (group as any).count === 'number') {
      return (group as any).count;
    }
    const items = Array.isArray(group) ? group : (group?.items || []);
    const first = items[0];
    return (first as any)?.count ?? 0;
  }

  getRankClass(rank: number): string {
    return `rank-${rank}`;
  }
}
