import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface TopKeywordItem {
  tag_id: number;
  name_zh: string;
  name: string;
  color: string | null;
  count: number;
}

/** 同次數的關鍵字一組（可並排顯示） */
export interface TopKeywordGroup {
  count: number;
  items: TopKeywordItem[];
}

export interface TopKeywordsResponse {
  groups: TopKeywordGroup[];
  days: number;
}

export interface OrientationStats {
  upright_count: number;
  reversed_count: number;
  total: number;
}

export interface TopCardStat {
  card_id: number;
  name_zh: string;
  name: string;
  card_type: string;
  suit_id: number | null;
  suit_name_zh: string | null;
  count: number;
}

export interface DistributionItem {
  key: string;
  label: string;
  count: number;
  color: string;
}

export interface SelectedCardRankingItem {
  card_id: number;
  name_zh: string;
  name: string;
  card_type: string;
  suit_id: number | null;
  suit_name_zh: string | null;
  selected_count: number;
  hit_count: number;
}

export interface KeywordTrendSeries {
  tag_id: number;
  name_zh: string;
  name: string;
  color: string | null;
  total: number;
  data: number[];
}

export interface AnalysisDashboardResponse {
  days: number;
  orientation: OrientationStats;
  top_cards: TopCardStat[];
  arcana_suit_distribution: DistributionItem[];
  selected_cards_ranking: SelectedCardRankingItem[];
  keyword_trend: {
    date_labels: string[];
    series: KeywordTrendSeries[];
  };
}

@Injectable({
  providedIn: 'root'
})
export class AnalysisService {
  private apiUrl = '/api/analysis';

  constructor(private http: HttpClient) {}

  /**
   * 取得過去 N 天 TOP3 關鍵字
   * @param days 天數，預設 30
   */
  getTopKeywords(days: number = 30): Observable<{ data: TopKeywordsResponse }> {
    const params = new HttpParams().set('days', days.toString());
    return this.http.get<{ data: TopKeywordsResponse }>(`${this.apiUrl}/top-keywords`, { params });
  }

  /**
   * 取得分析儀表板資料
   */
  getDashboard(days: number = 30): Observable<{ data: AnalysisDashboardResponse }> {
    const params = new HttpParams().set('days', days.toString());
    return this.http.get<{ data: AnalysisDashboardResponse }>(`${this.apiUrl}/dashboard`, { params });
  }
}
