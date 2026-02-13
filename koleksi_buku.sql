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