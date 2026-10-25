from pathlib import Path

from PIL import Image
from docx import Document
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.oxml.ns import qn
from docx.shared import Inches, Pt, RGBColor


ROOT = Path(__file__).resolve().parent
SCREENSHOTS = ROOT / "website_settings_screenshots"
CROPS = ROOT / "website_settings_crops"
OUTPUT = ROOT / "Manual Tetapan Website dan Halaman Utama.docx"

GREEN = "173D32"
INK = "1E2C27"
GRAY = "60706C"


# The sources are full-page browser captures. Every crop removes the admin sidebar/top bar
# and keeps only the form or homepage section that the reader needs to compare.
CROP_MAP = {
    "cover-identity-admin.jpg": ("01-admin-settings-full.jpg", (315, 250, 1465, 760)),
    "01-identity-admin.jpg": ("01-admin-settings-full.jpg", (315, 250, 1465, 760)),
    "01-identity-header.jpg": ("02-homepage-full.jpg", (135, 0, 650, 54)),
    "02-footer-logo-admin.jpg": ("01-admin-settings-full.jpg", (690, 320, 1465, 750)),
    "02-footer-branding.jpg": ("02-homepage-full.jpg", (0, 5520, 650, 6233)),
    "03-contact-admin.jpg": ("01-admin-settings-full.jpg", (315, 740, 1465, 1380)),
    "03-contact-footer.jpg": ("02-homepage-full.jpg", (1000, 5520, 1512, 6020)),
    "04-about-admin.jpg": ("01-admin-settings-full.jpg", (315, 1580, 1465, 2075)),
    "04-about-home.jpg": ("02-homepage-full.jpg", (0, 960, 1512, 1810)),
    "05-package-heading-admin.jpg": ("01-admin-settings-full.jpg", (315, 1900, 1465, 2040)),
    "05-package-heading-home.jpg": ("02-homepage-full.jpg", (0, 1960, 1512, 2520)),
    "06-terms-admin.jpg": ("01-admin-settings-full.jpg", (315, 2260, 1465, 2470)),
    "06-terms-footer.jpg": ("02-homepage-full.jpg", (760, 6000, 1512, 6233)),
}


def set_font(run, size=None, bold=None, color=None):
    run.font.name = "Arial"
    run._element.rPr.rFonts.set(qn("w:ascii"), "Arial")
    run._element.rPr.rFonts.set(qn("w:hAnsi"), "Arial")
    if size is not None:
        run.font.size = Pt(size)
    if bold is not None:
        run.bold = bold
    if color is not None:
        run.font.color.rgb = RGBColor.from_string(color)


def crop_screenshots():
    CROPS.mkdir(exist_ok=True)
    for crop_name, (source_name, box) in CROP_MAP.items():
        source = SCREENSHOTS / source_name
        if not source.exists():
            raise FileNotFoundError(source)
        with Image.open(source) as image:
            image.crop(box).save(CROPS / crop_name, quality=96, optimize=True)


def add_text(doc, text, size=10, bold=False, color=INK, alignment=None, after=4):
    paragraph = doc.add_paragraph()
    paragraph.paragraph_format.space_after = Pt(after)
    paragraph.paragraph_format.line_spacing = 1.08
    if alignment is not None:
        paragraph.alignment = alignment
    run = paragraph.add_run(text)
    set_font(run, size, bold, color)
    return paragraph


def add_heading(doc, text, level=1):
    paragraph = doc.add_paragraph(style=f"Heading {level}")
    paragraph.paragraph_format.space_before = Pt(0 if level == 1 else 7)
    paragraph.paragraph_format.space_after = Pt(4)
    run = paragraph.add_run(text)
    set_font(run, 15.5 if level == 1 else 11.5, True, "000000")
    return paragraph


def add_label(doc, text):
    paragraph = doc.add_paragraph()
    paragraph.paragraph_format.space_before = Pt(1)
    paragraph.paragraph_format.space_after = Pt(2)
    run = paragraph.add_run(text)
    set_font(run, 8.5, True, GREEN)
    return paragraph


def add_image(doc, filename, caption, width=6.2):
    path = CROPS / filename
    if not path.exists():
        raise FileNotFoundError(path)
    paragraph = doc.add_paragraph()
    paragraph.alignment = WD_ALIGN_PARAGRAPH.CENTER
    paragraph.paragraph_format.space_after = Pt(1)
    paragraph.add_run().add_picture(str(path), width=Inches(width))
    caption_paragraph = doc.add_paragraph()
    caption_paragraph.alignment = WD_ALIGN_PARAGRAPH.CENTER
    caption_paragraph.paragraph_format.space_after = Pt(3)
    caption_run = caption_paragraph.add_run(caption)
    set_font(caption_run, 8.2, False, GRAY)
    caption_run.italic = True


def add_pair(doc, title, instruction, admin_file, admin_caption, public_file, public_caption, admin_width=6.2, public_width=6.2):
    add_heading(doc, title)
    add_text(doc, instruction, 9.3, False, INK, after=3)
    add_label(doc, "A  DIISI DALAM ADMIN")
    add_image(doc, admin_file, admin_caption, admin_width)
    add_label(doc, "B  DITUNJUKKAN DI HALAMAN UTAMA")
    add_image(doc, public_file, public_caption, public_width)
    doc.add_page_break()


def configure_document(doc):
    section = doc.sections[0]
    section.top_margin = Inches(0.52)
    section.bottom_margin = Inches(0.52)
    section.left_margin = Inches(0.70)
    section.right_margin = Inches(0.70)

    styles = doc.styles
    styles["Normal"].font.name = "Arial"
    styles["Normal"]._element.rPr.rFonts.set(qn("w:ascii"), "Arial")
    styles["Normal"]._element.rPr.rFonts.set(qn("w:hAnsi"), "Arial")
    styles["Normal"].font.size = Pt(10)
    for style_name in ("Title", "Heading 1", "Heading 2"):
        style = styles[style_name]
        style.font.name = "Arial"
        style._element.rPr.rFonts.set(qn("w:ascii"), "Arial")
        style._element.rPr.rFonts.set(qn("w:hAnsi"), "Arial")
        style.font.color.rgb = RGBColor(0, 0, 0)

    header = section.header.paragraphs[0]
    header.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    set_font(header.add_run("SALAMHAJI TRAVEL & TOURS  |  MANUAL BERGAMBAR"), 8, False, GRAY)

    footer = section.footer.paragraphs[0]
    footer.alignment = WD_ALIGN_PARAGRAPH.CENTER
    set_font(footer.add_run("Rujukan tetapan website  |  Oktober 2026"), 8, False, GRAY)


def build():
    crop_screenshots()
    doc = Document()
    configure_document(doc)

    title = doc.add_paragraph(style="Title")
    title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    title.paragraph_format.space_after = Pt(8)
    set_font(title.add_run("Manual Tetapan Website dan Paparan Halaman Utama"), 24, False, "000000")
    add_text(
        doc,
        "Rujukan ini menunjukkan medan Tetapan Website yang perlu diisi dan tempat maklumat tersebut muncul pada halaman utama kepada pelanggan.",
        12.2,
        False,
        GRAY,
        WD_ALIGN_PARAGRAPH.CENTER,
        10,
    )
    add_image(doc, "cover-identity-admin.jpg", "Contoh crop admin: hanya maklumat Identiti syarikat, tanpa sidebar dan bar atas.")
    add_heading(doc, "Cara guna manual ini", 2)
    add_text(doc, "1. Isi atau kemas kini nilai dalam gambar A.  2. Rujuk gambar B untuk mengesahkan lokasi data tersebut pada halaman utama.  3. Simpan tetapan, kemudian semak semula halaman utama dalam pelayar.", 9.7, False, INK, after=6)
    add_text(doc, "Nota: screenshot ini menggunakan nilai yang sedang ada dalam Tetapan Website. Medan kosong tidak akan memaparkan nilai baharu sehingga ia diisi dan disimpan.", 9.4, True, GREEN, after=0)
    doc.add_page_break()

    add_pair(
        doc,
        "1 Identiti pada header website",
        "Masukkan Nama paparan, Tagline dan Logo utama. Ketiga-tiga nilai ini membentuk identiti yang pengunjung lihat pada bahagian atas halaman utama.",
        "01-identity-admin.jpg",
        "Admin: Nama paparan, Tagline dan Logo utama dalam Identiti syarikat.",
        "01-identity-header.jpg",
        "Halaman utama: logo, nama paparan dan tagline pada header.",
    )
    add_pair(
        doc,
        "2 Identiti dan logo pada footer",
        "Gunakan Logo footer Darul Soffi untuk logo di ruangan Hubungi Kami. Nama berdaftar digunakan pada baris hak cipta di bawah footer.",
        "02-footer-logo-admin.jpg",
        "Admin: medan Logo footer Darul Soffi dalam seksyen Identiti syarikat.",
        "02-footer-branding.jpg",
        "Halaman utama: logo footer dan identiti SalamHaji pada bahagian kiri footer.",
        admin_width=4.8,
        public_width=3.2,
    )
    add_pair(
        doc,
        "3 Alamat peta dan lesen",
        "Masukkan alamat hubungan atau cawangan, pautan Google Maps dan nombor lesen MOTAC. Pada screenshot semasa, telefon, WhatsApp, e-mel dan waktu operasi masih kosong, jadi ia tidak dipaparkan sebagai maklumat tambahan pada footer.",
        "03-contact-admin.jpg",
        "Admin: alamat, pautan peta dan nombor lesen di dalam Hubungan alamat dan lesen.",
        "03-contact-footer.jpg",
        "Halaman utama: logo Darul Soffi, alamat hubungan dan butang Buka dalam Google Maps di footer.",
        public_width=4.2,
    )
    add_pair(
        doc,
        "4 Kandungan bahagian About",
        "Isi Eyebrow About, Tajuk About, Penerangan About, Label CTA About dan URL CTA About. Semua nilai ini berada dalam blok pengenalan selepas hero halaman utama.",
        "04-about-admin.jpg",
        "Admin: semua medan Kandungan homepage untuk bahagian About.",
        "04-about-home.jpg",
        "Halaman utama: eyebrow, tajuk, penerangan dan butang CTA About.",
    )
    add_pair(
        doc,
        "5 Tajuk bahagian pakej",
        "Gunakan Tajuk bahagian pakej untuk mengubah tajuk di atas senarai kategori pakej. Pada screenshot semasa medan ini kosong dan halaman utama menggunakan tajuk lalai Pilihan Pakej SalamHaji.",
        "05-package-heading-admin.jpg",
        "Admin: medan Tajuk bahagian pakej di bahagian bawah Kandungan homepage.",
        "05-package-heading-home.jpg",
        "Halaman utama: tajuk seksyen pakej diikuti kategori dan senarai pakej.",
    )
    add_pair(
        doc,
        "6 Terma syarat dan pautan undang undang",
        "Masukkan teks Terma dan Syarat yang telah diluluskan. Di halaman utama, pengunjung mencapai kandungan ini melalui pautan Terma dan Syarat dalam footer; kandungan penuh dibuka pada halaman berasingan.",
        "06-terms-admin.jpg",
        "Admin: ruang teks Terma dan Syarat.",
        "06-terms-footer.jpg",
        "Halaman utama: pautan Dasar Privasi dan Terma dan Syarat pada bahagian bawah footer.",
    )

    doc.save(OUTPUT)
    print(OUTPUT)


if __name__ == "__main__":
    build()
