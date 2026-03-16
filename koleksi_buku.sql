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