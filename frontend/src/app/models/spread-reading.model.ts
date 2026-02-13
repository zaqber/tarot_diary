import { TarotCard } from './tarot-card.model';

export interface SpreadCardItem {
  position_number: number;
  card_id: number;
  is_reversed: boolean;
  card: TarotCard;
}

export interface SpreadTypeInfo {
  id: number;
  name: string;
  name_zh: string;
  card_count: number;
}

export interface SpreadReadingDetail {
  id: number;
  user_id: number;
  spread_type_id: number;
  spread_type: SpreadTypeInfo | null;
  reading_date: string;
  reading_time: string;
  question: string | null;
  overall_note: string | null;
  is_reviewed: boolean;
  spread_cards: SpreadCardItem[];
}
