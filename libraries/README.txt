External JS libraries (PptxGenJS, jsPDF, html2canvas) are loaded from CDN
in includes/footer.php for the editor page.

If your host has no internet access at runtime, download these files and place them here:

  libraries/pptxgenjs/pptxgen.bundle.js
    https://cdn.jsdelivr.net/gh/gitbrent/PptxGenJS@3.12.0/dist/pptxgen.bundle.js

  libraries/jspdf/jspdf.umd.min.js
    https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js

  libraries/html2canvas/html2canvas.min.js
    https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js

Then edit includes/footer.php to point the <script src="..."> tags at these local files.
