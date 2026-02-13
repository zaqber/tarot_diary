import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { SpreadReadingDetail } from '../models/spread-reading.model';

export interface CreateSpreadResponse {
  id: number;
  spread_type_id: number;
  reading_date: string;
}

export interface AddCardResponse {
  spread_card_id: number;
  position_number: number;
  card_id: number;
}

@Injectable({
  providedIn: 'root'
})
export class SpreadService {
  private apiUrl = '/api/spread-readings';

  constructor(private http: HttpClient) {}

  /**
   * 建立一筆新的三張牌陣
   */
  createSpreadReading(): Observable<{ data: CreateSpreadResponse }> {
    return this.http.post<{ data: CreateSpreadResponse }>(this.apiUrl, {});
  }

  /**
   * 為牌陣的某個位置紀錄一張牌
   */
  addCard(readingId: number, positionNumber: number, cardId: number, isReversed = false): Observable<{ data: AddCardResponse }> {
    return this.http.post<{ data: AddCardResponse }>(`${this.apiUrl}/${readingId}/cards`, {
      position_number: positionNumber,
      card_id: cardId,
      is_reversed: isReversed
    });
  }

  /**
   * 取得牌陣詳情（含每張牌名稱、tags）
   */
  getSpreadReading(id: number): Observable<{ data: SpreadReadingDetail }> {
    return this.http.get<{ data: SpreadReadingDetail }>(`${this.apiUrl}/${id}`);
  }

  /**
   * 取得當天的牌陣（一筆），若該日尚無紀錄會 404
   */
  getTodayReading(): Observable<{ data: SpreadReadingDetail }> {
    return this.http.get<{ data: SpreadReadingDetail }>(this.apiUrl, {
      params: { date: 'today' }
    });
  }

  /**
   * 取得牌陣列表（分頁，供 history 用）
   */
  getReadingList(params?: { per_page?: number; page?: number }): Observable<{
    data: SpreadReadingListItem[];
    meta: { current_page: number; last_page: number; per_page: number; total: number };
  }> {
    return this.http.get<{ data: SpreadReadingListItem[]; meta: any }>(this.apiUrl, {
      params: params ?? {}
    });
  }
}

export interface SpreadReadingListItem {
  id: number;
  reading_date: string;
  spread_cards: Array<{
    position_number: number;
    card_id: number;
    card: { id: number; name: string; name_zh: string } | null;
  }>;
}
