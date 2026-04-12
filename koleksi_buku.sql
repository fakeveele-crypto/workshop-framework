CREATE TABLE kategori (
    idkategori INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(100) NOT NULL
);

CREATE TABLE buku (
    idbuku INT PRIMARY KEY AUTO_INCREMENT,
    kode VARCHAR(20) NOT NULL,
    judul VARCHAR(500) NOT NULL,
    pengarang VARCHAR(200),
    idkategori INT,
    CONSTRAINT FK_kategori FOREIGN KEY (idkategori) 
    REFERENCES kategori(idkategori) ON DELETE CASCADE ON UPDATE CASCADE
);

ALTER TABLE "public"."buku" 
ADD CONSTRAINT fk_buku_kategori 
FOREIGN KEY ("idkategori") 
REFERENCES "public"."kategori" ("idkategori") 
ON DELETE CASCADE 
ON UPDATE CASCADE;

ALTER TABLE "public"."buku" 
ADD CONSTRAINT fk_buku_kategori 
FOREIGN KEY ("idkategori") 
REFERENCES "public"."kategori" ("idkategori") 
ON DELETE CASCADE 
ON UPDATE CASCADE;

ALTER TABLE "public"."buku" 
DROP CONSTRAINT fk_kategori;

CREATE TABLE penjualan (
   id_penjualan SERIAL PRIMARY KEY,
   timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
   total INTEGER NOT NULL
);

CREATE TABLE penjualan_detail (
   idpenjualan_detail SERIAL PRIMARY KEY,
   id_penjualan INTEGER NOT NULL,
   id_barang VARCHAR(8) NOT NULL,
   jumlah SMALLINT NOT NULL,
   subtotal INTEGER NOT NULL,
   CONSTRAINT fk_penjualan FOREIGN KEY (id_penjualan) REFERENCES penjualan(id_penjualan),
   CONSTRAINT fk_barang FOREIGN KEY (id_barang) REFERENCES barang(id_barang)
);

CREATE TABLE vendor (
   idvendor SERIAL PRIMARY KEY,
   nama_vendor varchar(255) 
);

CREATE TABLE menu (
   idmenu SERIAL PRIMARY KEY, 
   nama_menu varchar(255), 
   harga int, 
   path_gambar varchar(255),
   idvendor int, 
   CONSTRAINT fk_vendor FOREIGN KEY (idvendor) REFERENCES vendor(idvendor)
);

CREATE TABLE pesanan (
   idpesanan SERIAL PRIMARY KEY, 
   nama varchar(255), 
   timestamp timestamp, 
   total int, 
   metode_bayar int, 
   status_bayar smallint 
);

CREATE TABLE detail_pesanan (
   iddetail_pesanan SERIAL PRIMARY KEY, 
   idmenu int, 
   idpesanan int, 
   jumlah int, 
   harga  int, 
   subtotal int, 
   timestamp timestamp, 
   catatan varchar(255), 
   CONSTRAINT fk_pesanan FOREIGN KEY (idpesanan) REFERENCES pesanan(idpesanan),
   CONSTRAINT fk_menu FOREIGN KEY (idmenu) REFERENCES menu(idmenu)
);

INSERT INTO vendor (nama_vendor) VALUES 
('Stan Bakso Solo'),
('Soto Ayam Lamongan'),
('Nasi Goreng Gila'),
('Mie Ayam Mas No'),
('Ayam Geprek Juara'),
('Es Teh Solo Mantap'),
('Gado-Gado Surabaya'),
('Kopi Janji Manis'),
('Siomay Bandung Asli'),
('Pempek Palembang');

ALTER TABLE pesanan 
ADD COLUMN iduser INT,
ADD COLUMN external_id VARCHAR(255),
ADD COLUMN snap_token TEXT;