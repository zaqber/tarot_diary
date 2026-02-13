export interface Tag {
  id: number;
  name: string;
  name_zh: string;
  position: string;
  is_default?: boolean;
}

export interface TagApiResponse {
  success: boolean;
  message: string;
  data: Tag[];
}
