# LAPORAN PRAKTEK KERJA LAPANGAN

# PERANCANGAN CHATBOT INTERAKTIF BERBASIS

# WEB MENGGUNAKAN FRAMEWORK LARAVEL

# DI DINAS KOMUNIKASI DAN INFORMATIKA KOTA

# METRO

```
Diajukan untuk memenuhi persyaratan kelulusan
Mata Kuliah Praktek Kerja Lapangan (IF25-40030)
```
```
Oleh:
William Chan
122140130
```
## PROGRAM STUDI TEKNIK INFORMATIKA

## FAKULTAS TEKNOLOGI INDUSTRI

## INSTITUT TEKNOLOGI SUMATERA


```
Lembar Pengesahan Program Studi Teknik Informatika
```
```
PERANCANGAN CHATBOT INTERAKTIF MENGGUNAKAN
FRAMEWORK LARAVEL DAN RAG GEMINI
```
```
Di DINAS KOMUNIKASI DAN INFORMATIKA KOTA METRO
```
```
Oleh:
William Chan
122140130
```
```
disetujui dan disahkan sebagai
Laporan Praktek Kerja Lapangan
```
Lampung Selatan, ......
Pembimbing Praktek Kerja Lapangan Program Studi Teknik Informatika ITERA

_<ttd digital/basah sesuai kesepakatan>_

Aidil Afriansyah, S.Kom., M.Kom.
NIP: 19910416 2019 03 1 015


```
Lembar Pengesahan
```
```
PERANCANGAN CHATBOT INTERAKTIF BERBASIS WEB
MENGGUNAKAN FRAMEWORK LARAVEL DAN RAG GEMINI
```
```
Di DINAS KOMUNIKASI DAN INFORMATIKA KOTA METRO
```
```
oleh :
William Chan
122140130
```
```
disetujui dan disahkan sebagai
Laporan Praktek Kerja Lapangan
```
Metro, 11 Agustus 2025
Kepala Bidang Informatika dan Persandian

Andi Setiyono, ST.
NIP. 19750527 2002 12 1 006


## ABSTRAK

_Tuliskan ringkasan laporan Kerja Praktek, yang merupakan ringkasan dari lingkup
kerja praktek (termasuk nama perusahaan, penjelasan singkat terkait permasalahan
di instansi, pelaksanaan kerja praktek (proses dan pencapaian hasil), kesimpulan
umum mengenai kerja praktek yang telah dilakukan dan kata kunci, dalam Bahasa
Indonesia. (dituliskan_ **_dalam maksimal 250 kata & hanya dalam 1 halaman_** _)
Kata kunci: ..._


## Kata Pengantar

_Tuliskan rasa terimakasih kepada siapa saja yang terkait dengan Praktek Kerja
Lapangan ini. Penulisan Kata Pengantar dengan bahasa Indonesia yang baik dan
benar._


## Daftar Isi


- ABSTRAK
- Kata Pengantar
- Bab I Pendahuluan
   - 1.1. Latar Belakang
   - 1.2. Rumusan Masalah
   - 1.3. Tujuan
   - 1.4. Manfaat
   - 1.5. Ruang Lingkup
   - 1.6. Sistematika Penulisan
- Bab II Gambaran Umum Instansi
   - 2.1. Profil Organisasi
   - 2.2. Visi dan Misi Organisasi................................................................................
   - 2.3. Struktur Organisasi
   - 2.4. Deskripsi Pekerjaan
   - 2.5. Jadwal Kerja
- Bab III Landasan Teori
   - 3.1. Dasar Teori
   - 3.2. Teori I
   - 3.3. Teori II
      - 3.3.1. Sub Teori I
- Bab IV Metode Penelitian
   - 4.1. Analisis Permasalahan
   - 4.2. Alur Penyelesaian
   - 4.3. Gambaran Umum Sistem/Aplikasi/Prototype
   - 4.4. Alat dan Bahan...............................................................................................
      - 4.4.1. Alat........................................................................................................
   - 4.5. Metodologi Pengembangan
- Bab V Hasil Implementasi
   - 5.1. Hasil Implementasi
   - 5.2. Evaluasi dan Analisis
- Bab VI Kesimpulan dan Saran
   - 6.1. Kesimpulan
   - 6.2. Saran
- Referensi
- Lampiran A. TOR (Term of Reference)
- Lampiran B. Log Sheet
- Lampiran C. Dokumen Teknik
- Lampiran D. Dokumentasi Kegiatan


```
<Daftar lain-lain>
```
_Dapat ditambahkan berbagai daftar yang dibutuhkan seperti daftar tabel, daftar
gambar, daftar algoritma, daftar padanan istilah, daftar singkatan, daftar istilah,
daftar simbol. Khusus untuk daftar pustaka (Referensi), dapat diletakkan setelah bab
Kesimpulan, sebelum lampiran. Jika hanya terdapat satu gambar atau satu tabel,
maka tidak perlu dibuat daftar gambar atau daftar tabel. Setiap daftar, misal daftar
gambar, daftar tabel, daftar istilah dan singkatan, semuanya diletakkan pada halaman
terpisah._
Perancangan Backend Pada Sistem Tiket Pelanggan


## Bab I Pendahuluan

_Bagian ini berisi pendahuluan mengenai Praktek Kerja Lapangan (PKL) yang
dilaksanakan. Dokumen PKL ditulis dalam font Times New Roman dengan ukuran font
12pt. Margin halaman adalah 3 cm (1.18 in) untuk sisi atas, bawah, & kanan, dan 3.
cm (1.38 in) untuk sisi kiri._

### 1.1. Latar Belakang

_Laporan PKL adalah sebuah laporan penelitian yang berhubungan dengan
pemecahan masalah (problem solving) berkaitan dengan Informatika, yang terjadi
pada instansi PKL [1].
Tuliskanlah latar belakang dari pelaksanaan PKL di perusahaan dan
substansi yang digeluti berkaitan dengan tujuan, misi, visi atau fungsi perusahaan.
Tuliskan permasalahan terkait dengan apa yang menjadi permasalahan di instansi
tersebut, lalu jelaskan solusi untuk mengatasi permasalahan tersebut. Solusi yang
ditulis berkaitan dengan ilmu informatika yang telah dipelajari selama kuliah._
**_Jangan menjelaskan tentang latar belakang pelaksanaan mata kuliah
Praktek Kerja Lapangan_**_._

### 1.2. Rumusan Masalah

_Tuliskanlah permasalahan utama apa yang mendasari anda dalam melakukan
PKL. Bisa ditulis dalam beberapa poin. Rumusan Masalah akan dijawab di bagian
Bab VI Kesimpulan._

### 1.3. Tujuan

_Tuliskanlah tujuan dari penyelesaian permasalahan bukan tujuan PKL seperti
ditulis kurikulum. Tujuan harus selaras dengan Rumusan Masalah dan akan berkaitan
dengan Kesimpulan._

### 1.4. Manfaat

_Tuliskanlah manfaat anda melaksanakan PKL dan dalam menyelesaikan
permasalahan di tempat kerja praktek, untuk secara pribadi, prodi Teknik
Informatika, dan instansi tempat bekerja._


### 1.5. Ruang Lingkup

_Tuliskanlah ruang lingkup dan batasan untuk permasalahan dan penyelesaian
anda. Jika anda bekerja dalam tim maka tuliskan juga ruang lingkup kerja praktek
anda sebagai bagian dari tim._

### 1.6. Sistematika Penulisan

_Tuliskan sistematika substansi dari draft laporan kerja praktek anda, dimulai
dari_ **_Bab I s.d Bab VI_** _termasuk cakupan dari setiap Bab yang dituliskan._

● **Bab I Pendahuluan //Seharusnya jadi Sub-Bab 1.6.**

_Jelaskan apa yang tertulis pada Bab I sesuai dengan topik penelitian_

● **Bab II Gambaran Umum Instansi**

_Jelaskan apa yang tertulis pada Bab II sesuai dengan topik penelitian_

● **Bab III Landasan Teori**

_Jelaskan apa yang tertulis pada Bab III sesuai dengan topik penelitian_

● **Bab IV Metode Penelitian**

_Jelaskan apa yang tertulis pada Bab IV sesuai dengan topik penelitian_

● **Bab V Hasil Implementasi**

_Jelaskan apa yang tertulis pada Bab V sesuai dengan topik penelitian_

● **Bab VI Kesimpulan dan Saran**

_Jelaskan apa yang tertulis pada Bab VI sesuai dengan topik penelitian_


## Bab II Gambaran Umum Instansi

```
Jelaskan dengan struktur mengenai organisasi atau lingkungan kerja praktik
```
### 2.1. Profil Organisasi

Gambar 2.1. Contoh _Logo Perusahaan
Tuliskan profil terkait organisasi tempat anda melaksanakan PKL. Profil
berisi deskripsi umum, sejarah, key people, dll. Harus terdapat logo perusahaan._

### 2.2. Visi dan Misi Organisasi................................................................................

```
Tuliskan visi misi terkait organisasi tempat anda melaksanakan PKL.
```
### 2.3. Struktur Organisasi

_Tuliskanlah struktur organisasi perusahaan PKL dan jelaskan posisi tim PKL
pada struktur organisasi tersebut. Dapat digambarkan dalam bentuk diagram._

Gambar 2.2. Contoh Struktur Organisasi
_Dalam gambar struktur organisasi, tekankan tempat unit atau divisi
pelaksanaan PKL dengan menggunakan shading atau garis putus-putus._


### 2.4. Deskripsi Pekerjaan

_Tuliskan deskripsi setiap tahap pekerjaan yang dilakukan, dilengkapi dengan
deskripsi pekerjaan mahasiswa PKL terkait dengan divisi tempat mahasiswa bekerja.
Kaitkan pekerjaan dengan proyek yang dikerjakan selama pelaksanaan PKL. Jelaskan
pekerjaan berdasarkan tanggal mulai sampai tanggal berakhirnya PKL.
Mahasiswa juga boleh menuliskan semua pekerjaan yang dilaksanakan
selama PKL, yang mungkin tidak terlalu berhubungan dengan proyek utama PKL._

### 2.5. Jadwal Kerja

_Tuliskan gambaran jadwal kegiatan selama PKL, yaitu waktu pelaksanaan
PKL dan jam kerja pada tiap harinya. Jelaskan juga rentang tanggal bekerja selama
PKL, termasuk jika ada hari libur nasional._ **_Waktu pelaksanaan PKL adalah
sebanyak 25-40 hari kerja._** _Rinciannya dapat mengacu ke Lampiran B. Log Sheet_.


## Bab III Landasan Teori

_Pada bagian ini dijelaskan mengenai konsep dan dasar teori yang digunakan
dalam implementasi sistem, aplikasi, atau prototype yang dikembangkan._

### 3.1. Dasar Teori

_Berisi teori/konsep yang berkaitan/digunakan dalam kerja praktek.
Gunakanlah data melalui buku/jurnal referensi, publikasi tugas akhir, penelitian,
buku, dan informasi web yang dapat dipertanggungjawabkan,_ **_hindari penggunaan
dasar teori melalui tautan Wikipedia, surat kabar, atau portal berita, yang dapat
memiliki isi yang tidak bersifat fakta_**_._

### 3.2. Teori I

_Deskripsikan mengenai teori/konsep yang berkaitan/digunakan/menjadi
acuan dalam penelitian. Kemudian berikan pembahasan sederhana mengenai
penggunaannya di dalam laporan PKL yang Anda kerjakan._

### 3.3. Teori II

_Deskripsikan mengenai teori / konsep yang berkaitan / digunakan / menjadi
acuan dalam penelitian. Kemudian berikan pembahasan sederhana mengenai
penggunaannya di dalam tugas akhir yang Anda kerjakan._

#### 3.3.1. Sub Teori I

_Sub Teori dapat dituliskan dengan level penomoran 3 & style “Heading 3”_


## Bab IV Metode Penelitian

_Pada bagian ini dijelaskan mengenai pelaksanaan PKL meliputi deskripsi
persoalan, alur proses penyelesaian, gambaran umum, dan metodologi
pengembangan dari perangkat lunak, keras, sistem, dan prototype yang
dikembangkan atau dibangun. Adapun rincian dari substansi pada Bab IV adalah
sebagai subbab berikut, tetapi subbab dapat disesuaikan kembali dengan output yang
anda kerjakan pada tempat kerja praktek anda.
Caption tabel dan gambar ditulis dengan format <bab>.<urutan
tabel/gambar>. Caption tabel berada di atas tabel, sedangkan caption gambar berada
di bawah gambar._

```
Tabel 4.1. Contoh caption tabel
```
```
Gambar 4.1. Contoh caption gambar
```
### 4.1. Analisis Permasalahan

_Tuliskan analisis dari persoalan yang harus diselesaikan dalam kerja praktek,
termasuk usulan solusi untuk persoalan tersebut. Sesuaikan dengan latar belakang
persoalan, deskripsi persoalan, batasan yang harus diselesaikan dalam PKL,_


_teknologi terkait yang mendukung solusi dari persoalan, dan hal lain yang terkait
dengan persoalan PKL._

### 4.2. Alur Penyelesaian

_Tuliskan alur penyelesaian PKL anda dengan detail, dari mulai observasi,
identifikasi masalah, perumusan masalah, pemilihan metode, implementasi,
pengujian, dan penarikan kesimpulan. Supaya lebih tergambar dengan jelas, bisa
dengan menggunakan flowchart._

### 4.3. Gambaran Umum Sistem/Aplikasi/Prototype

_Gambarkanlah secara umum terkait arsitektur sistem/aplikasi/prototype yang
anda kembangkan. Dapat menggunakan diagram atau gambar sebagai pembantu._

### 4.4. Alat dan Bahan...............................................................................................

_Uraikan spesifikasi dari alat dan bahan yang digunakan untuk mendukung
pengembangan sistem atau prototype._

#### 4.4.1. Alat........................................................................................................

_Alat yang digunakan untuk melakukan pengembangan sistem atau prototype,
dapat berupa komputer, PC, Arduino, raspberry, dsb._

1. Notebook, dengan spesifikasi...
2. Smartphone, dengan spesifikasi...
3. Software XYZ

**4.4.2. Bahan**
_Bahan yang digunakan/diperlukan untuk melakukan pengembangan sistem
atau prototype, dapat berupa:_

1. Dataset pihak pertama yang diperoleh secara langsung, melalui izin instansi.
2. Dataset pihak lain yang diperoleh dengan izin atau dalam lisensi yang diizinkan
    untuk digunakan secara langsung.
3. Dataset pihak pertama yang disusun sendiri melalui kuesioner, observasi, atau
    interview.


### 4.5. Metodologi Pengembangan

_Uraikan secara detail terkait dengan metodologi yang anda gunakan untuk
membangun dan mengembangkan sistem atau prototype, termasuk alur
pengembangan, desain/diagram sistem, rencana pengembangan, cara pengumpulan
data, low/high fidelity, dsb._


## Bab V Hasil Implementasi

_Tuliskan bagaimana hasil dari implementasi dari perangkat lunak, perangkat
keras, sistem, atau prototype yang anda kembangkan. Sertakan juga lingkup
implementasi anda dalam merancang perangkat lunak, perangkat keras, sistem, atau
prototype._

### 5.1. Hasil Implementasi

_Tuliskan hasil dari proses pengembangan. Tuliskan hasil pengujian dari
semua fitur-fitur yang sudah dirancang pada bab IV berdasarkan metodologi yang
digunakan selama menyelesaikan dan mengembangkan perangkat lunak, keras,
sistem, atau prototype._

### 5.2. Evaluasi dan Analisis

_Tuliskan evaluasi dan analisis sederhana, dapat berupa tabel, visualisasi data,
atau narasi, berdasarkan hasil implementasi dan pengujian yang anda dapatkan.
Evaluasi dapat menceritakan hasil presentasi terhadap instansi bersangkutan,
termasuk stakeholder yang terlibat dan tanggal presentasi._


## Bab VI Kesimpulan dan Saran

_Tuliskan apa yang perlu disampaikan sebagai penutup berupa kesimpulan dan
saran PKL._

### 6.1. Kesimpulan

_Tuliskan kesimpulan baik mengenai proses pelaksanaan PKL maupun
mengenai substansi yang dikerjakan selama PKL. Kesimpulan harus menjawab semua
poin Rumusan Masalah & sesuai dengan Tujuan yang terdapat dalam Bab I._

### 6.2. Saran

_Tuliskan saran terhadap penelitian (berdasarkan observasi & analisis hasil)
untuk kedepannya. Saran juga dapat berkaitan mengenai proses pelaksanaan PKL
selama bekerja di instansi, agar dapat menjadi lebih baik kedepannya._


## Referensi

_Tuliskan berbagai referensi yang digunakan dalam laporan PKL terurut abjad
berdasar nama pengarang dan beri nomor mulai dari [1], dengan gaya IEEE
Referensi yang digunakan_ **_minimal 10_** _(diperbolehkan mengambil referensi
dari website, tetapi tidak boleh lebih dari 3)._


_Penulisan halaman untuk setiap lampiran sama dengan format penulisan halaman
untuk setiap bab, contoh untuk Lampiran A, halaman berawal dari A-1, A-2, dst. Posisi
nomor halaman pada halaman pertama ditulis pada bottom center, untuk halaman
berikutnya adalah top right. Hal ini juga berlaku untuk nomor halaman pada bab isi._

## Lampiran C. Dokumen Teknik

_Mahasiswa PKL dapat melampirkan berbagai dokumen teknik yang
merupakan hasil pelaksanaan PKL, contoh_ **_Software Requirement Specification
(SRS), Manual Book, Kode Program, dll_**_. Lampiran ini wajib ada, kecuali bagi
perusahaan yang menyatakan bahwa dokumen teknis terkait PKL bersifat
confidential. Jika dokumen teknis bersifat confidential, maka lampiran ini diganti
dengan lampiran surat pernyataan dari perusahaan dan ditandatangani oleh
pembimbing di perusahaan bahwa_ **_dokumen teknis terkait PKL bersifat confidential._**