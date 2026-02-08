import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { TarotCardService } from '../../services/tarot-card.service';
import { TarotCard } from '../../models/tarot-card.model';

@Component({
  selector: 'app-detail',
  templateUrl: './detail.component.html',
  styleUrls: ['./detail.component.css']
})
export class DetailComponent implements OnInit {
  card: TarotCard | null = null;
  loading: boolean = true;
  error: string | null = null;
  cardId: number | null = null;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private tarotCardService: TarotCardService
  ) { }

  ngOnInit(): void {
    this.route.params.subscribe(params => {
      const id = params['id'];
      if (id) {
        this.cardId = +id;
        this.loadCardDetail(this.cardId);
      }
    });
  }

  loadCardDetail(id: number): void {
    this.loading = true;
    this.error = null;

    this.tarotCardService.getCardById(id).subscribe({
      next: (response) => {
        this.card = response.data;
        this.loading = false;
      },
      error: (error) => {
        console.error('Error loading card detail:', error);
        this.error = '無法載入塔羅牌詳細資訊';
        this.loading = false;
      }
    });
  }

  getCardImage(id: number): string {
    return this.tarotCardService.getCardImagePath(id);
  }

  goBack(): void {
    this.router.navigate(['/setting']);
  }

  getCardTypeText(type: string): string {
    // card_type 現在已經是翻譯後的文字，直接返回
    return type;
  }
}

