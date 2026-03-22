import { Component, OnInit, OnDestroy } from '@angular/core';
import { Router } from '@angular/router';
import { Subject } from 'rxjs';
import { debounceTime, distinctUntilChanged, takeUntil } from 'rxjs/operators';
import { TarotCardService } from '../../services/tarot-card.service';
import { TarotCard, TagItem } from '../../models/tarot-card.model';

@Component({
  selector: 'app-tarot-card-mgmt',
  templateUrl: './tarot-card-mgmt.component.html',
  styleUrls: ['./tarot-card-mgmt.component.css']
})
export class TarotCardMgmtComponent implements OnInit, OnDestroy {
  cards: TarotCard[] = [];
  loading: boolean = true;
  error: string | null = null;

  // Filter options
  selectedType: string = 'all';
  selectedSuit: number | null = null;
  searchKeyword: string = '';

  // Pagination
  currentPage: number = 1;
  totalPages: number = 1;
  totalCards: number = 0;
  perPage: number = 12;

  // Card position state (true = upright, false = reversed)
  cardPositions: Map<number, boolean> = new Map();

  // Search debounce
  private searchSubject = new Subject<string>();
  private destroy$ = new Subject<void>();

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
    this.searchSubject.pipe(
      debounceTime(300),
      distinctUntilChanged(),
      takeUntil(this.destroy$)
    ).subscribe(() => {
      this.currentPage = 1;
      this.loadCards();
    });

    this.loadCards();
  }

  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  loadCards(): void {
    this.loading = true;
    this.error = null;

    const params: any = {
      per_page: this.perPage,
      page: this.currentPage,
    };

    if (this.selectedType !== 'all') {
      params.card_type = this.selectedType as 'major' | 'minor';
    }
    if (this.selectedSuit !== null) {
      params.suit_id = this.selectedSuit;
    }
    if (this.searchKeyword.trim()) {
      params.search = this.searchKeyword.trim();
    }

    this.tarotCardService.getAllCards(params).subscribe({
      next: (response) => {
        this.cards = response.data;
        this.currentPage = response.meta.current_page;
        this.totalPages = response.meta.last_page;
        this.totalCards = response.meta.total;
        // Initialize new cards as upright
        this.cards.forEach(card => {
          if (!this.cardPositions.has(card.id)) {
            this.cardPositions.set(card.id, true);
          }
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

  onTypeChange(type: string): void {
    this.selectedType = type;
    if (type === 'major') {
      this.selectedSuit = null;
    }
    this.currentPage = 1;
    this.loadCards();
  }

  onSuitChange(suitId: number | null): void {
    this.selectedSuit = suitId;
    if (suitId !== null) {
      this.selectedType = 'minor';
    }
    this.currentPage = 1;
    this.loadCards();
  }

  onSearchChange(): void {
    this.searchSubject.next(this.searchKeyword);
  }

  clearFilters(): void {
    this.selectedType = 'all';
    this.selectedSuit = null;
    this.searchKeyword = '';
    this.currentPage = 1;
    this.loadCards();
  }

  // Pagination
  goToPage(page: number): void {
    if (page < 1 || page > this.totalPages || page === this.currentPage) {
      return;
    }
    this.currentPage = page;
    this.loadCards();
  }

  get pageNumbers(): number[] {
    const pages: number[] = [];
    const maxVisible = 5;
    let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
    let end = Math.min(this.totalPages, start + maxVisible - 1);

    if (end - start + 1 < maxVisible) {
      start = Math.max(1, end - maxVisible + 1);
    }

    for (let i = start; i <= end; i++) {
      pages.push(i);
    }
    return pages;
  }

  get rangeStart(): number {
    return (this.currentPage - 1) * this.perPage + 1;
  }

  get rangeEnd(): number {
    return Math.min(this.currentPage * this.perPage, this.totalCards);
  }

  // Card helpers
  getCardImage(id: number): string {
    return this.tarotCardService.getCardImagePath(id);
  }

  viewCardDetail(cardId: number): void {
    this.router.navigate(['/detail', cardId]);
  }

  getCardTypeText(type: string): string {
    return type;
  }

  toggleCardPosition(event: Event, cardId: number): void {
    event.stopPropagation();
    const currentPosition = this.cardPositions.get(cardId) || true;
    this.cardPositions.set(cardId, !currentPosition);
  }

  isCardUpright(cardId: number): boolean {
    return this.cardPositions.get(cardId) !== false;
  }

  getPositionText(cardId: number): string {
    return this.isCardUpright(cardId) ? '正位' : '逆位';
  }

  getTags(card: TarotCard): TagItem[] {
    if (!card.tags?.active) {
      return [];
    }
    const isUpright = this.isCardUpright(card.id);
    const position = isUpright ? 'upright' : 'reversed';
    return card.tags.active.filter(tag => tag.position === position || tag.position === 'both');
  }

  getTagColorClass(index: number): string {
    const colors = ['tag-color-1', 'tag-color-2', 'tag-color-3', 'tag-color-4', 'tag-color-5', 'tag-color-6'];
    return colors[index % colors.length];
  }

  getMeaning(card: TarotCard): string {
    const isUpright = this.isCardUpright(card.id);
    const raw = isUpright
      ? card.official_meaning.upright
      : card.official_meaning.reversed;
    return (raw && String(raw).trim()) ? String(raw) : '（尚無官方牌義）';
  }
}
