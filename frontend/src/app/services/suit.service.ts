import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Suit {
  id: number;
  name: string;
  name_zh: string;
  element: string | null;
}

@Injectable({
  providedIn: 'root'
})
export class SuitService {
  private apiUrl = '/api/suits';

  constructor(private http: HttpClient) {}

  /**
   * 取得所有花色（手動抽牌時先選 suit，大牌則無 suit_id）
   */
  getSuits(): Observable<{ data: Suit[] }> {
    return this.http.get<{ data: Suit[] }>(this.apiUrl);
  }
}
