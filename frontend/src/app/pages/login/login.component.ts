import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent {
  mode: 'login' | 'register' = 'login';
  loading = false;
  error = '';

  loginForm = { email: '', password: '' };
  registerForm = {
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    display_name: ''
  };

  constructor(
    private auth: AuthService,
    private router: Router
  ) {}

  switchMode(m: 'login' | 'register'): void {
    this.mode = m;
    this.error = '';
  }

  onSubmitLogin(): void {
    this.error = '';
    this.loading = true;
    this.auth.login(this.loginForm.email, this.loginForm.password).subscribe({
      next: (res) => {
        if (res.success) this.router.navigate(['/']);
        else this.error = res.message;
        this.loading = false;
      },
      error: (err) => {
        this.error = err.error?.message || '登入失敗，請稍後再試。';
        this.loading = false;
      }
    });
  }

  onSubmitRegister(): void {
    this.error = '';
    if (this.registerForm.password !== this.registerForm.password_confirmation) {
      this.error = '兩次密碼輸入不一致';
      return;
    }
    this.loading = true;
    this.auth.register({
      username: this.registerForm.username,
      email: this.registerForm.email,
      password: this.registerForm.password,
      password_confirmation: this.registerForm.password_confirmation,
      display_name: this.registerForm.display_name || undefined
    }).subscribe({
      next: (res) => {
        if (res.success) this.router.navigate(['/']);
        else this.error = res.message;
        this.loading = false;
      },
      error: (err) => {
        this.error = err.error?.message || '註冊失敗，請稍後再試。';
        this.loading = false;
      }
    });
  }

  loginWithGoogle(): void {
    this.auth.loginWithGoogle();
  }
}
