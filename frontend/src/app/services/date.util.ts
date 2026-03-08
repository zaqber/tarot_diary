/**
 * 以台北時間（Asia/Taipei）為準的日期工具，與後端 APP_TIMEZONE 一致。
 */

const TAIPEI = 'Asia/Taipei';

/**
 * 取得「今天」在台北的日期字串 YYYY-MM-DD（供 API 查詢、比對用）
 */
export function getTodayDateStringInTaipei(): string {
  const formatter = new Intl.DateTimeFormat('en-CA', {
    timeZone: TAIPEI,
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  });
  const parts = formatter.formatToParts(new Date());
  const get = (type: string) => parts.find(p => p.type === type)?.value ?? '';
  return `${get('year')}-${get('month')}-${get('day')}`;
}

/**
 * 將 YYYY-MM-DD 格式為顯示用（例如 2025/02/14）
 * 若傳入為純日期字串則不涉時區轉換。
 */
export function formatDateDisplay(dateStr: string): string {
  if (!dateStr) return '—';
  const part = dateStr.trim().split('T')[0].split('-');
  if (part.length !== 3) return dateStr;
  const [y, m, d] = part;
  return `${y}/${m}/${d}`;
}
