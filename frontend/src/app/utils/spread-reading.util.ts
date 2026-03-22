/**
 * 是否至少有一張「對得到」的牌（spread_cards 有列且 card 關聯存在）
 */
export function hasReadableSpreadCards(
  spreadCards: Array<{ card?: unknown }> | null | undefined
): boolean {
  if (!spreadCards?.length) {
    return false;
  }
  return spreadCards.some(sc => sc.card != null);
}
