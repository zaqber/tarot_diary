import { Pipe, PipeTransform } from '@angular/core';
import { DomSanitizer, SafeHtml } from '@angular/platform-browser';
import { parse } from 'marked';
import * as DOMPurifyModule from 'dompurify';

/** dompurify 為 export=，bundler 可能掛在 default 上 */
function getDOMPurify(): { sanitize: (html: string, config?: object) => string } {
  const mod = DOMPurifyModule as unknown as {
    default?: { sanitize: (html: string, config?: object) => string };
    sanitize?: (html: string, config?: object) => string;
  };
  if (typeof mod.sanitize === 'function') {
    return mod as { sanitize: (html: string, config?: object) => string };
  }
  if (mod.default && typeof mod.default.sanitize === 'function') {
    return mod.default;
  }
  return mod as { sanitize: (html: string, config?: object) => string };
}

/**
 * 將 AI 回傳的 Markdown 轉為安全 HTML（供 innerHTML 使用）
 */
@Pipe({ name: 'markdownToHtml' })
export class MarkdownToHtmlPipe implements PipeTransform {
  constructor(private sanitizer: DomSanitizer) {}

  transform(value: string | null | undefined): SafeHtml {
    if (value == null || value.trim() === '') {
      return this.sanitizer.bypassSecurityTrustHtml('');
    }
    try {
      const rawHtml = parse(value, { async: false }) as string;
      const clean = getDOMPurify().sanitize(rawHtml, {
        ALLOWED_TAGS: [
          'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
          'p', 'br', 'div', 'span',
          'strong', 'em', 'b', 'i',
          'ul', 'ol', 'li',
          'blockquote',
          'a', 'code', 'pre', 'hr',
        ],
        ALLOWED_ATTR: ['href', 'target', 'rel', 'class'],
      });
      return this.sanitizer.bypassSecurityTrustHtml(clean);
    } catch {
      return this.sanitizer.bypassSecurityTrustHtml(
        '<p class="ai-md-fallback">無法格式化此段內容，請改以純文字閱讀。</p>'
      );
    }
  }
}
