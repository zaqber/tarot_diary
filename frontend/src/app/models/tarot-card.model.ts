// 花色介面
export interface Suit {
  id: number | null;
  name: string | null;
  name_zh: string | null;
  element: string | null;
}

// 牌義介面
export interface CardMeaning {
  upright: string;
  reversed: string;
}

// 圖片介面
export interface CardImages {
  upright: string | null;
  reversed: string | null;
}

// 標籤項目介面
export interface TagItem {
  id: number;
  name: string;
  name_zh: string;
  position: string;
  color?: string | null;
}

// 標籤集合介面
export interface CardTags {
  active: TagItem[];
  default: TagItem[];
  custom: TagItem[];
}

// 塔羅牌主介面
export interface TarotCard {
  id: number;
  name: string;
  name_zh: string;
  card_type: string;
  number: number;
  suit_id: number | null;
  suit?: Suit;
  official_meaning: CardMeaning;
  self_definition: CardMeaning;
  tags?: CardTags;
  images: CardImages;
  created_at: string;
  updated_at: string;
}

// 分頁 meta 介面
export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

// API 回應介面
export interface TarotCardListResponse {
  data: TarotCard[];
  meta: PaginationMeta;
}
