import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { SpreadReadingDetail } from '../models/spread-reading.model';
import { getTodayDateStringInTaipei } from './date.util';

export interface CreateSpreadResponse {
  id: number;
  spread_type_id: number;
  reading_date: string;
  reading_time?: string;
  theme?: string;
  theme_label_zh?: string;
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
  createSpreadReading(theme: string = 'overall', question?: string | null): Observable<{ data: CreateSpreadResponse }> {
    const body: { theme: string; question?: string } = { theme };
    const q = question?.trim();
    if (q) {
      body.question = q;
    }
    return this.http.post<{ data: CreateSpreadResponse }>(this.apiUrl, body);
  }

  /** 尚未抽牌前可變更該筆牌陣主題 */
  updateReadingTheme(readingId: number, theme: string): Observable<{
    data: { theme: string; theme_label_zh: string };
  }> {
    return this.http.patch<{ data: { theme: string; theme_label_zh: string } }>(
      `${this.apiUrl}/${readingId}/theme`,
      { theme }
    );
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
   * 使用台北時間的今日日期，與後端 APP_TIMEZONE=Asia/Taipei 一致
   */
  getTodayReading(): Observable<{ data: SpreadReadingDetail }> {
    const today = getTodayDateStringInTaipei();
    return this.http.get<{ data: SpreadReadingDetail }>(this.apiUrl, {
      params: { date: today }
    });
  }

  /**
   * 取得牌陣列表（分頁，供 history 用）
   * @param month YYYY-MM 僅該月
   * @param theme overall|love|career|finance
   * @param has_question 是否僅有／無填寫問題敘述
   */
  getReadingList(params?: {
    per_page?: number;
    page?: number;
    month?: string;
    theme?: string;
    has_question?: boolean;
  }): Observable<{
    data: SpreadReadingListItem[];
    meta: { current_page: number; last_page: number; per_page: number; total: number };
  }> {
    const query: Record<string, string | number | boolean> = {};
    if (params?.per_page != null) {
      query['per_page'] = params.per_page;
    }
    if (params?.page != null) {
      query['page'] = params.page;
    }
    if (params?.month) {
      query['month'] = params.month;
    }
    if (params?.theme) {
      query['theme'] = params.theme;
    }
    if (params?.has_question !== undefined) {
      query['has_question'] = params.has_question;
    }
    return this.http.get<{ data: SpreadReadingListItem[]; meta: any }>(this.apiUrl, {
      params: query
    });
  }

  /**
   * 儲存問題敘述（ai_question）
   */
  updateQuestion(readingId: number, question: string): Observable<{ data: { ai_question: string | null } }> {
    return this.http.patch<{ data: { ai_question: string | null } }>(
      `${this.apiUrl}/${readingId}/question`,
      { question }
    );
  }

  /**
   * 更新某個牌位的正逆位
   */
  updateCardOrientation(readingId: number, position: number, isReversed: boolean): Observable<{ data: { position_number: number; is_reversed: boolean } }> {
    return this.http.patch<{ data: { position_number: number; is_reversed: boolean } }>(
      `${this.apiUrl}/${readingId}/cards/positions/${position}/orientation`,
      { is_reversed: isReversed }
    );
  }

  /**
   * 切換「符合當天狀態」的標籤（點選後紀錄）
   */
  toggleSpreadCardTag(readingId: number, position: number, tagId: number): Observable<{ data: { selected: boolean; selected_tag_ids: number[] } }> {
    return this.http.post<{ data: { selected: boolean; selected_tag_ids: number[] } }>(
      `${this.apiUrl}/${readingId}/cards/positions/${position}/tags`,
      { tag_id: tagId }
    );
  }

  /**
   * 請 AI（後端預設 Gemini）依主題＋三張牌解牌
   */
  requestAiInterpret(
    readingId: number,
    question?: string | null,
    topic?: string | null
  ): Observable<{ data: SpreadReadingDetail }> {
    const body: { question?: string; topic?: string } = {};
    const q = question?.trim();
    if (q) body.question = q;
    const t = topic?.trim();
    if (t) body.topic = t;
    return this.http.post<{ data: SpreadReadingDetail }>(
      `${this.apiUrl}/${readingId}/ai-interpret`,
      body
    );
  }
}

export interface SpreadReadingListItem {
  id: number;
  reading_date: string;
  /** ISO 8601，同日多筆時用於區分順序 */
  reading_time?: string | null;
  /** overall | love | career | finance */
  theme?: string;
  theme_label_zh?: string;
  /** 問題敘述（選填） */
  ai_question?: string | null;
  spread_cards: Array<{
    position_number: number;
    card_id: number;
    is_reversed?: boolean;
    card: { id: number; name: string; name_zh: string } | null;
  }>;
}
