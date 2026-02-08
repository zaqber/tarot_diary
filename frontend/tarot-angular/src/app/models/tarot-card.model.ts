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

// 關鍵字介面
export interface CardKeywords {
  upright: string[];
  reversed: string[];
}

// 圖片介面
export interface CardImages {
  upright: string | null;
  reversed: string | null;
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
  keywords: CardKeywords;
  images: CardImages;
  created_at: string;
  updated_at: string;
}

// API 回應介面
export interface TarotCardListResponse {
  data: TarotCard[];
}

