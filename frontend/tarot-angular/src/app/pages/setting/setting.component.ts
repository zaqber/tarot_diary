import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { TarotCardService } from '../../services/tarot-card.service';
import { TarotCard } from '../../models/tarot-card.model';

@Component({
  selector: 'app-setting',
  templateUrl: './setting.component.html',
  styleUrls: ['./setting.component.css']
})
export class SettingComponent implements OnInit {
  cards: TarotCard[] = [];
  filteredCards: TarotCard[] = [];
  loading: boolean = true;
  error: string | null = null;
  
  // Filter options
  selectedType: string = 'all';
  selectedSuit: number | null = null;
  searchKeyword: string = '';

  // Card position state (true = upright, false = reversed)
  cardPositions: Map<number, boolean> = new Map();

  // Suit options
  suits = [
    { id: 1, name: '權杖' },
    { id: 2, name: '聖杯' },
    { id: 3, name: '寶劍' },
    { id: 4, name: '錢幣' }
  ];

  constructor(
    private tarotCardService: TarotCardService,
    private router: Router
  ) { }

  ngOnInit(): void {
    this.loadAllCards();
  }

  loadAllCards(): void {
    this.loading = true;
    this.error = null;

    this.tarotCardService.getAllCards({ per_page: 100 }).subscribe({
      next: (response) => {
        this.cards = response.data;
        this.filteredCards = [...this.cards];
        // Initialize all cards as upright
        this.cards.forEach(card => {
          this.cardPositions.set(card.id, true);
        });
        this.loading = false;
      },
      error: (error) => {
        console.error('Error loading cards:', error);
        this.error = '無法載入塔羅牌列表';
        this.loading = false;
      }
    });
  }

  applyFilters(): void {
    this.filteredCards = this.cards.filter(card => {
      // Type filter
      // 注意：card_type 現在是翻譯後的文字（如 "大牌"、"小牌"），需要特殊處理
      if (this.selectedType !== 'all') {
        const cardTypeMatch = this.selectedType === 'major' 
          ? (card.card_type === '大牌' || card.card_type === 'Major Arcana')
          : (card.card_type === '小牌' || card.card_type === 'Minor Arcana');
        if (!cardTypeMatch) {
          return false;
        }
      }

      // Suit filter (only for minor cards)
      if (this.selectedSuit && card.suit && card.suit.id !== this.selectedSuit) {
        return false;
      }

      // Search keyword filter
      if (this.searchKeyword) {
        const keyword = this.searchKeyword.toLowerCase();
        return card.name.toLowerCase().includes(keyword) || 
               card.name_zh.toLowerCase().includes(keyword);
      }

      return true;
    });
  }

  onTypeChange(type: string): void {
    this.selectedType = type;
    if (type === 'major') {
      this.selectedSuit = null; // Clear suit filter for major arcana
    }
    this.applyFilters();
  }

  onSuitChange(suitId: number | null): void {
    this.selectedSuit = suitId;
    if (suitId !== null) {
      this.selectedType = 'minor'; // Automatically set to minor when selecting suit
    }
    this.applyFilters();
  }

  onSearchChange(): void {
    this.applyFilters();
  }

  clearFilters(): void {
    this.selectedType = 'all';
    this.selectedSuit = null;
    this.searchKeyword = '';
    this.filteredCards = [...this.cards];
  }

  getCardImage(id: number): string {
    return this.tarotCardService.getCardImagePath(id);
  }

  viewCardDetail(cardId: number): void {
    this.router.navigate(['/detail', cardId]);
  }

  getCardTypeText(type: string): string {
    // card_type 現在已經是翻譯後的文字，直接返回
    return type;
  }

  // Toggle card position between upright and reversed
  toggleCardPosition(event: Event, cardId: number): void {
    event.stopPropagation(); // Prevent triggering card detail navigation
    const currentPosition = this.cardPositions.get(cardId) || true;
    this.cardPositions.set(cardId, !currentPosition);
  }

  // Check if card is upright
  isCardUpright(cardId: number): boolean {
    return this.cardPositions.get(cardId) !== false;
  }

  // Get position text
  getPositionText(cardId: number): string {
    return this.isCardUpright(cardId) ? '正位' : '逆位';
  }

  // Get keywords for current position
  getKeywords(card: TarotCard): string {
    const isUpright = this.isCardUpright(card.id);
    const keywords = isUpright ? card.keywords.upright : card.keywords.reversed;
    return keywords && keywords.length > 0 ? keywords.join('、') : '無關鍵字';
  }

  // Get tags for current position (deprecated - new API doesn't return tags)
  getTags(card: TarotCard): any[] {
    return [];
  }

  // Get meaning for current position
  getMeaning(card: TarotCard): string {
    const isUpright = this.isCardUpright(card.id);
    return isUpright ? 
      card.official_meaning.upright : 
      card.official_meaning.reversed;
  }
}
