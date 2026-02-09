import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';
import { TarotCard, TarotCardListResponse } from '../models/tarot-card.model';

@Injectable({
  providedIn: 'root'
})
export class TarotCardService {
  private apiUrl = '/api/tarot-cards';

  constructor(private http: HttpClient) {}

  /**
   * 取得所有塔羅牌列表
   * 支援分頁、篩選和搜尋功能
   * 
   * @param params 查詢參數
   * @returns Observable<TarotCardListResponse>
   */
  getAllCards(params?: {
    per_page?: number;
    page?: number;
    card_type?: 'major' | 'minor';
    suit_id?: number;
    search?: string;
  }): Observable<TarotCardListResponse> {
    let httpParams = new HttpParams();

    if (params) {
      if (params.per_page) {
        httpParams = httpParams.set('per_page', params.per_page.toString());
      }
      if (params.page) {
        httpParams = httpParams.set('page', params.page.toString());
      }
      if (params.card_type) {
        httpParams = httpParams.set('card_type', params.card_type);
      }
      if (params.suit_id) {
        httpParams = httpParams.set('suit_id', params.suit_id.toString());
      }
      if (params.search) {
        httpParams = httpParams.set('search', params.search);
      }
    }

    return this.http.get<TarotCardListResponse>(this.apiUrl, { params: httpParams });
  }

  /**
   * 搜尋塔羅牌（已整合到 getAllCards 中，保留此方法以保持向後兼容）
   * @deprecated 建議直接使用 getAllCards({ search: keyword })
   */
  searchCards(keyword: string, perPage: number = 15): Observable<TarotCardListResponse> {
    return this.getAllCards({
      search: keyword,
      per_page: perPage
    });
  }

  /**
   * 根據卡片類型取得塔羅牌（已整合到 getAllCards 中，保留此方法以保持向後兼容）
   * @deprecated 建議直接使用 getAllCards({ card_type: type })
   */
  getCardsByType(type: 'major' | 'minor', perPage: number = 15): Observable<TarotCardListResponse> {
    return this.getAllCards({
      card_type: type,
      per_page: perPage
    });
  }

  /**
   * 根據花色取得塔羅牌（已整合到 getAllCards 中，保留此方法以保持向後兼容）
   * @deprecated 建議直接使用 getAllCards({ suit_id: suitId })
   */
  getCardsBySuit(suitId: number, perPage: number = 15): Observable<TarotCardListResponse> {
    return this.getAllCards({
      suit_id: suitId,
      per_page: perPage
    });
  }

  /**
   * 取得單張塔羅牌詳細資訊
   * 
   * @param id 塔羅牌 ID
   * @returns Observable<{ data: TarotCard }>
   */
  getCardById(id: number): Observable<{ data: TarotCard }> {
    return this.http.get<{ data: TarotCard }>(`${this.apiUrl}/${id}`);
  }

  /**
   * 取得塔羅牌圖片路徑
   * 如果卡片有 images.upright，使用該 URL，否則使用本地圖片
   * 
   * @param card 塔羅牌物件
   * @param isReversed 是否為逆位
   * @returns 圖片路徑
   */
  getCardImageUrl(card: TarotCard, isReversed: boolean = false): string {
    if (isReversed && card.images?.reversed) {
      return card.images.reversed;
    }
    if (!isReversed && card.images?.upright) {
      return card.images.upright;
    }
    // 如果沒有圖片 URL，使用本地圖片
    return this.getCardImagePath(card.id);
  }

  /**
   * 取得塔羅牌圖片路徑（本地資源）
   * 
   * @param id 塔羅牌 ID
   * @returns 本地圖片路徑
   */
  getCardImagePath(id: number): string {
    return `assets/images/tarot_cards/${id}.png`;
  }

  /**
   * 取得封面圖片路徑
   * 
   * @returns 封面圖片路徑
   */
  getCoverImagePath(): string {
    return 'assets/images/tarot_cards/0.png';
  }
}

