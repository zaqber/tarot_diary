import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-auth-callback',
  templateUrl: './auth-callback.component.html',
  styleUrls: ['./auth-callback.component.css']
})
export class AuthCallbackComponent implements OnInit {
  error = '';

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private auth: AuthService
  ) {}

  ngOnInit(): void {
    const token = this.route.snapshot.queryParamMap.get('token');
    if (!token) {
      this.error = '未取得登入憑證，請重試。';
      return;
    }
    this.auth.setTokenFromCallback(token);
    this.auth.me().subscribe({
      next: () => this.router.navigate(['/']),
      error: () => {
        this.auth.clearAuth();
        this.error = '登入驗證失敗，請重試。';
      }
    });
  }
}
