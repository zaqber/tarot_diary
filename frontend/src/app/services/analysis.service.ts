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
}
