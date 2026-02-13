import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';
import { TagApiResponse } from '../models/tag.model';

@Injectable({
  providedIn: 'root'
})
export class TagService {
  private apiUrl = '/api/tarot-cards';

  constructor(private http: HttpClient) {}

  getActiveTags(cardId: number): Observable<TagApiResponse> {
    return this.http.get<TagApiResponse>(`${this.apiUrl}/${cardId}/tags/active`);
  }

  addCustomTags(cardId: number, tags: { name_zh: string; position: string }[]): Observable<TagApiResponse> {
    return this.http.post<TagApiResponse>(`${this.apiUrl}/${cardId}/tags/custom`, { tags });
  }

  deleteCustomTags(cardId: number, tags: { tag_id: number; position?: string }[]): Observable<TagApiResponse> {
    const options = {
      headers: new HttpHeaders({ 'Content-Type': 'application/json' }),
      body: { tags }
    };
    return this.http.delete<TagApiResponse>(`${this.apiUrl}/${cardId}/tags/custom`, options);
  }

  resetTags(cardId: number): Observable<TagApiResponse> {
    return this.http.post<TagApiResponse>(`${this.apiUrl}/${cardId}/tags/reset`, {});
  }
}
