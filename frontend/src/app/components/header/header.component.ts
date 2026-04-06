import { Component, OnInit, HostListener } from '@angular/core';
import { AuthService, AuthUser } from '../../services/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-header',
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.css']
})
export class HeaderComponent implements OnInit {
  isMenuOpen = false;
  user: AuthUser | null = null;

  constructor(
    public auth: AuthService,
    private router: Router
  ) {}

  ngOnInit(): void {
    this.user = this.auth.getStoredUser();
    if (this.auth.isLoggedIn() && !this.user) {
      this.auth.me().subscribe({
        next: (res) => this.user = res.data ?? null
      });
    }
  }

  toggleMenu(): void {
    this.isMenuOpen = !this.isMenuOpen;
  }

  logout(): void {
    this.auth.logout().subscribe({
      next: () => {
        this.user = null;
        this.router.navigate(['/']);
      }
    });
  }

  @HostListener('document:click', ['$event'])
  onDocumentClick(event: Event): void {
    const target = event.target as HTMLElement;
    const clickedInside = target.closest('.nav') || target.closest('.menu-toggle');

    if (!clickedInside && this.isMenuOpen) {
      this.isMenuOpen = false;
    }
  }

  /** History 列表與單筆閱讀頁皆視為「紀錄」導覽 */
  isHistoryNavActive(): boolean {
    const path = this.router.url.split('?')[0].split('#')[0];
    return path === '/history' || path.startsWith('/reading/');
  }
}
