import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, Router } from '@angular/router';
import { TarotCardService } from '../../services/tarot-card.service';
import { TagService } from '../../services/tag.service';
import { TarotCard } from '../../models/tarot-card.model';
import { Tag } from '../../models/tag.model';

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

  // Tag 相關
  activeTags: Tag[] = [];
  uprightTags: Tag[] = [];
  reversedTags: Tag[] = [];

  // Modal 相關
  showTagModal: boolean = false;
  showResetConfirm: boolean = false;
  editingPosition: string = 'upright';
  newTagName: string = '';
  tagLoading: boolean = false;

  constructor(
    private route: ActivatedRoute,
    private router: Router,
    private tarotCardService: TarotCardService,
    private tagService: TagService
  ) { }

  ngOnInit(): void {
    this.route.params.subscribe(params => {
      const id = params['id'];
      if (id) {
        this.cardId = +id;
        this.loadCardDetail(this.cardId);
        this.loadActiveTags(this.cardId);
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

  loadActiveTags(cardId: number): void {
    this.tagService.getActiveTags(cardId).subscribe({
      next: (response) => {
        this.activeTags = response.data;
        this.splitTagsByPosition();
      },
      error: (error) => {
        console.error('Error loading tags:', error);
      }
    });
  }

  splitTagsByPosition(): void {
    this.uprightTags = this.activeTags.filter(
      t => t.position === 'upright' || t.position === 'both'
    );
    this.reversedTags = this.activeTags.filter(
      t => t.position === 'reversed' || t.position === 'both'
    );
  }

  getTagColorClass(index: number): string {
    return 'tag-color-' + ((index % 6) + 1);
  }

  // Modal 操作
  openTagEditor(position: string): void {
    this.editingPosition = position;
    this.newTagName = '';
    this.showTagModal = true;
  }

  closeTagEditor(): void {
    this.showTagModal = false;
  }

  get currentPositionTags(): Tag[] {
    return this.editingPosition === 'upright' ? this.uprightTags : this.reversedTags;
  }

  get positionLabel(): string {
    return this.editingPosition === 'upright' ? '正位' : '逆位';
  }

  // Tag 操作
  addTag(): void {
    if (!this.newTagName.trim() || !this.cardId) return;

    this.tagLoading = true;
    const tags = [{ name_zh: this.newTagName.trim(), position: this.editingPosition }];

    this.tagService.addCustomTags(this.cardId, tags).subscribe({
      next: () => {
        this.newTagName = '';
        this.tagLoading = false;
        this.loadActiveTags(this.cardId!);
      },
      error: (error) => {
        console.error('Error adding tag:', error);
        this.tagLoading = false;
      }
    });
  }

  removeTag(tag: Tag): void {
    if (!this.cardId) return;

    this.tagLoading = true;
    const tags = [{ tag_id: tag.id, position: tag.position }];

    this.tagService.deleteCustomTags(this.cardId, tags).subscribe({
      next: (response) => {
        this.activeTags = response.data;
        this.splitTagsByPosition();
        this.tagLoading = false;
      },
      error: (error) => {
        console.error('Error removing tag:', error);
        this.tagLoading = false;
      }
    });
  }

  confirmResetTags(): void {
    this.showResetConfirm = true;
  }

  cancelReset(): void {
    this.showResetConfirm = false;
  }

  resetTags(): void {
    if (!this.cardId) return;

    this.showResetConfirm = false;
    this.tagLoading = true;
    this.tagService.resetTags(this.cardId).subscribe({
      next: () => {
        this.tagLoading = false;
        this.loadActiveTags(this.cardId!);
      },
      error: (error) => {
        console.error('Error resetting tags:', error);
        this.tagLoading = false;
      }
    });
  }

  getCardImage(id: number): string {
    return this.tarotCardService.getCardImagePath(id);
  }

  goBack(): void {
    this.router.navigate(['/tarot_card_mgmt']);
  }

  getCardTypeText(type: string): string {
    return type;
  }
}
