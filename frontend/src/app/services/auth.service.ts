import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable, tap } from 'rxjs';

const TOKEN_KEY = 'auth_token';
const USER_KEY = 'auth_user';

export interface AuthUser {
  id: number;
  username: string;
  email: string;
  display_name: string | null;
}

export interface AuthResponse {
  user: AuthUser;
  token: string;
  token_type: string;
}

export interface AuthApiResponse<T> {
  success: boolean;
  message: string;
  data?: T;
}

@Injectable({
  providedIn: 'root'
})
export class AuthService {
  private apiUrl = '/api/auth';

  constructor(private http: HttpClient) {}

  getToken(): string | null {
    return localStorage.getItem(TOKEN_KEY);
  }

  private setToken(token: string): void {
    localStorage.setItem(TOKEN_KEY, token);
  }

  getStoredUser(): AuthUser | null {
    const raw = localStorage.getItem(USER_KEY);
    if (!raw) return null;
    try {
      return JSON.parse(raw) as AuthUser;
    } catch {
      return null;
    }
  }

  private setStoredUser(user: AuthUser): void {
    localStorage.setItem(USER_KEY, JSON.stringify(user));
  }

  clearAuth(): void {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
  }

  isLoggedIn(): boolean {
    return !!this.getToken();
  }

  register(data: {
    username: string;
    email: string;
    password: string;
    password_confirmation: string;
    display_name?: string;
  }): Observable<AuthApiResponse<AuthResponse>> {
    return this.http
      .post<AuthApiResponse<AuthResponse>>(`${this.apiUrl}/register`, data)
      .pipe(
        tap((res) => {
          if (res.success && res.data) {
            this.setToken(res.data.token);
            this.setStoredUser(res.data.user);
          }
        })
      );
  }

  login(email: string, password: string): Observable<AuthApiResponse<AuthResponse>> {
    return this.http
      .post<AuthApiResponse<AuthResponse>>(`${this.apiUrl}/login`, { email, password })
      .pipe(
        tap((res) => {
          if (res.success && res.data) {
            this.setToken(res.data.token);
            this.setStoredUser(res.data.user);
          }
        })
      );
  }

  logout(): Observable<AuthApiResponse<null>> {
    return this.http
      .post<{ success: boolean; message: string }>(`${this.apiUrl}/logout`, {}, {
        headers: this.authHeaders()
      })
      .pipe(tap(() => this.clearAuth()));
  }

  /** 取得目前登入使用者（會更新 localStorage 中的 user） */
  me(): Observable<AuthApiResponse<AuthUser>> {
    return this.http
      .get<AuthApiResponse<AuthUser>>(`${this.apiUrl}/me`, {
        headers: this.authHeaders()
      })
      .pipe(
        tap((res) => {
          if (res.success && res.data) this.setStoredUser(res.data);
        })
      );
  }

  /** 導向後端 Google OAuth（後端會再 redirect 到 Google，完成後導回 /auth/callback?token=xxx） */
  loginWithGoogle(): void {
    window.location.href = '/api/auth/google';
  }

  /** 從 Google callback 取得 token 後由前端儲存（在 callback 頁呼叫，只寫入 token） */
  setTokenFromCallback(token: string): void {
    this.setToken(token);
  }

  authHeaders(): { [key: string]: string } {
    const token = this.getToken();
    return token ? { Authorization: `Bearer ${token}` } : {};
  }
}
