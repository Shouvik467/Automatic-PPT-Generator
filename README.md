# AI PPT Generator

Create stunning AI-generated presentations from any topic in seconds — powered by the [Pollinations AI](https://pollinations.ai) API.

Built with **HTML, CSS, vanilla JavaScript, and Core PHP** only.
No database. No framework. No build step. Works on XAMPP, WAMP, localhost, and any shared PHP host (including Hostinger).

![screenshot](assets/images/hero-preview.png)

---

## ✨ Features

- 🤖 **AI-Generated Outline & Content** — turns any topic into a full deck (title → intro → agenda → sections → takeaways → thank you)
- 🎨 **14 Visual Themes** — Modern, Corporate, Futuristic, Dark Pro, Glassmorphism, Nature, and more
- 🖼️ **Auto AI Images** — every suitable slide gets a unique Pollinations image in your chosen style
- 📐 **20+ Slide Layouts** — title, bullets, split, statistics, comparison, timeline, process, quote, takeaways, thank-you, etc.
- ✏️ **Visual Slide Editor** — inline editing, layout switching, image upload, background/gradient, per-slide notes, undo/redo
- 🎬 **Full-Screen Slideshow** — keyboard, touch swipe, auto-play, timer, speaker notes, thumbnails, transitions
- 📤 **Export** — PowerPoint (.pptx), PDF, PNG slides, portable JSON, print
- 🌓 **Light / Dark Mode** — smooth theme switch, persists in `localStorage`, follows system preference
- 💾 **No Database** — every presentation stored as a JSON file under `storage/presentations/`
- 🔒 **Secure API Key Handling** — the Pollinations key stays server-side in `config/pollinations-config.php`
- 📱 **Fully Responsive** — works on phone, tablet, laptop, desktop

---

## 📁 Project Structure

```
ai-ppt-generator/
├── index.php
├── generator.php
├── outline.php
├── editor.php
├── slideshow.php
├── saved-presentations.php
│
├── api/
│   ├── generate-outline.php
│   ├── generate-content.php
│   ├── generate-presentation.php
│   ├── generate-image.php
│   ├── regenerate-slide.php
│   ├── save-presentation.php
│   ├── load-presentation.php
│   ├── delete-presentation.php
│   └── test-api.php
│
├── config/
│   ├── config.php
│   └── pollinations-config.php   ← put your API key here
│
├── includes/
│   ├── header.php
│   ├── sidebar.php
│   ├── footer.php
│   └── functions.php
│
├── assets/
│   ├── css/ (style, generator, editor, slideshow, responsive)
│   ├── js/  (app, theme, generator, outline, editor, export, autosave, slideshow)
│   ├── images/
│   └── icons/
│
├── storage/
│   ├── presentations/    ← saved .json presentations
│   ├── generated-images/ ← optional local image cache
│   ├── uploads/
│   ├── temp/
│   └── logs/             ← app.log
│
├── libraries/            ← optional offline copies of PptxGenJS / jsPDF / html2canvas
└── README.md
```

---

## 🚀 Installation

### 1. Requirements

- **PHP 7.4 or newer** (PHP 8.x recommended)
- Extensions: `curl`, `json`, `mbstring`, `openssl`
- Any web server: Apache, Nginx, XAMPP, WAMP, MAMP, Hostinger, etc.

### 2. Copy the files

Drop the entire `ai-ppt-generator/` folder into your web root:

| Host        | Destination                             | URL                                       |
|-------------|-----------------------------------------|-------------------------------------------|
| XAMPP       | `C:\xampp\htdocs\ai-ppt-generator\`     | http://localhost/ai-ppt-generator/        |
| WAMP        | `C:\wamp64\www\ai-ppt-generator\`       | http://localhost/ai-ppt-generator/        |
| MAMP        | `/Applications/MAMP/htdocs/…`            | http://localhost:8888/ai-ppt-generator/   |
| Hostinger   | `public_html/ai-ppt-generator/`         | https://yourdomain.com/ai-ppt-generator/  |
| Linux/Nginx | `/var/www/html/ai-ppt-generator/`       | https://yourdomain.com/ai-ppt-generator/  |

### 3. Permissions

Make sure the `storage/` folder is **writable** by PHP:

```bash
chmod -R 775 storage
```

(On Windows / XAMPP this is usually automatic.)

### 4. Configure the Pollinations API key

Open `config/pollinations-config.php` and paste your key:

```php
define('POLLINATIONS_API_KEY', 'sk-your-actual-key-here');
```

Get a key at: <https://enter.pollinations.ai/sign-in>

> **Note:** the key is optional. Leaving it empty (`''`) still works because Pollinations offers free public endpoints with rate limits. Adding a key removes those limits and grants faster responses.

### 5. Open the app

Visit `http://localhost/ai-ppt-generator/` (or your equivalent URL). Enter any topic on the homepage and click **Generate Presentation**.

---

## 🧭 Usage Flow

1. **Homepage** → type a topic, click *Generate Presentation*.
2. **Setup form** → pick number of slides, tone, audience, theme, image style, aspect ratio, language.
3. **Outline** → review, edit titles/summaries, drag to reorder, add/delete, regenerate individually.
4. **Build** → progress bar shows content + image generation, slide by slide.
5. **Editor** → visual editor with sidebar (slides), toolbar (regen, present, export), and property panel.
6. **Slideshow** → full-screen presentation with keyboard, touch, auto-play, notes.
7. **Export** → PPTX, PDF, PNG images, JSON, or print.

### Keyboard shortcuts (slideshow)

| Key      | Action           |
|----------|------------------|
| → / Space | Next slide       |
| ← | Previous slide   |
| Home / End | First / Last slide |
| P | Play / Pause auto-advance |
| N | Toggle speaker notes |
| F | Fullscreen       |
| Esc | Exit fullscreen  |

### Keyboard shortcuts (editor)

| Key       | Action  |
|-----------|---------|
| Ctrl / ⌘ + Z | Undo |
| Ctrl / ⌘ + Y | Redo |

---

## 🔐 Security notes

- The Pollinations API key lives **only in `config/pollinations-config.php`** — it never reaches the browser.
- All AI calls go through PHP endpoints in `api/`.
- `config/`, `includes/`, and non-image files inside `storage/` are protected by `.htaccess` (Apache).
- On Nginx, add equivalent `location` blocks to deny direct access to those folders.

Example Nginx snippet:

```nginx
location ~ ^/(config|includes)/ { deny all; }
location ~ ^/storage/.*\.(json|log|txt|php)$ { deny all; }
```

---

## 🧩 Extending

- **Add a new slide layout:** register the layout name in `assets/js/editor.js` and `slideshow.js` (`SlideRenderer.html`) and add matching CSS in `assets/css/editor.css`.
- **Add a new theme:** append the theme name to `themePicker` in `generator.php`, add CSS rules under `.slide[data-theme="..."]` in `editor.css`, and register colors in `assets/js/export.js → themeColors()` for PPTX export.
- **Change AI model:** edit `config/pollinations-config.php` (`POLLINATIONS_TEXT_MODEL`, `POLLINATIONS_IMAGE_MODEL`).

---

## 🧪 Troubleshooting

| Problem | Fix |
|--------|-----|
| Blank page or 500 error | Check `storage/logs/app.log` and PHP error log. Ensure PHP ≥ 7.4 with `curl` enabled. |
| “API unreachable” in the footer | Server can’t reach `text.pollinations.ai`. Whitelist it, or check your host’s outbound network policy. |
| Images not appearing | The image URL fetches from Pollinations directly. If your host blocks external images in `<img>`, enable the local download flag by calling `/api/generate-image.php` with `download: true`. |
| PPTX exports without images | Some browsers block cross-origin images. Regenerate images so they cache, or set your host to fetch and store them locally. |
| Editor toolbar overflows on mobile | It scrolls horizontally by design — swipe left/right. |
| Cannot save presentation | `storage/presentations/` must be writable by the PHP user. |

---

## 📜 License

Free for personal, educational, and commercial use.

Built on top of:
- [Pollinations AI](https://pollinations.ai) — AI text & image generation
- [PptxGenJS](https://gitbrent.github.io/PptxGenJS/) — PowerPoint export
- [jsPDF](https://parall.ax/products/jspdf) — PDF export
- [html2canvas](https://html2canvas.hertzen.com/) — DOM → canvas snapshots

Fonts: [Inter](https://rsms.me/inter/) & [Space Grotesk](https://fonts.google.com/specimen/Space+Grotesk) via Google Fonts.
