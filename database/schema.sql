-- ============================================================
-- Schema: Platform Penggalangan Dana - PeduliBersama
-- Target: Supabase SQL Editor
-- ============================================================

-- 1. TABEL KAMPANYE (Campaigns)
CREATE TABLE IF NOT EXISTS kampanye (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES auth.users(id) ON DELETE SET NULL,
    judul VARCHAR(255) NOT NULL,
    deskripsi TEXT,
    kategori VARCHAR(100) DEFAULT 'Umum',
    target_dana BIGINT NOT NULL,
    dana_terkumpul BIGINT DEFAULT 0,
    gambar_url TEXT,
    batas_waktu DATE,
    status VARCHAR(20) DEFAULT 'aktif' CHECK (status IN ('aktif', 'selesai', 'dibatalkan')),
    created_at TIMESTAMPTZ DEFAULT now(),
    updated_at TIMESTAMPTZ DEFAULT now()
);

-- 2. TABEL DONASI (Donations)
CREATE TABLE IF NOT EXISTS donasi (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    kampanye_id UUID NOT NULL REFERENCES kampanye(id) ON DELETE CASCADE,
    nama_donatur VARCHAR(255) NOT NULL,
    email_donatur VARCHAR(255),
    jumlah BIGINT NOT NULL CHECK (jumlah > 0),
    pesan TEXT,
    metode_pembayaran VARCHAR(50) DEFAULT 'transfer',
    status VARCHAR(20) DEFAULT 'berhasil',
    created_at TIMESTAMPTZ DEFAULT now()
);

-- 3. INDEXES
CREATE INDEX IF NOT EXISTS idx_kampanye_user_id ON kampanye(user_id);
CREATE INDEX IF NOT EXISTS idx_kampanye_status ON kampanye(status);
CREATE INDEX IF NOT EXISTS idx_donasi_kampanye_id ON donasi(kampanye_id);
CREATE INDEX IF NOT EXISTS idx_donasi_created_at ON donasi(created_at DESC);

-- 4. TRIGGER: AUTO-UPDATE updated_at
CREATE OR REPLACE FUNCTION update_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = now();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trigger_kampanye_updated_at ON kampanye;
CREATE TRIGGER trigger_kampanye_updated_at
    BEFORE UPDATE ON kampanye
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at();

-- 5. TRIGGER: AUTO-UPDATE dana_terkumpul
CREATE OR REPLACE FUNCTION update_dana_terkumpul()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE kampanye
    SET dana_terkumpul = (
        SELECT COALESCE(SUM(jumlah), 0)
        FROM donasi
        WHERE kampanye_id = NEW.kampanye_id AND status = 'berhasil'
    )
    WHERE id = NEW.kampanye_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trigger_update_dana_terkumpul ON donasi;
CREATE TRIGGER trigger_update_dana_terkumpul
    AFTER INSERT ON donasi
    FOR EACH ROW
    EXECUTE FUNCTION update_dana_terkumpul();

-- 6. ROW LEVEL SECURITY (RLS)
ALTER TABLE kampanye ENABLE ROW LEVEL SECURITY;
ALTER TABLE donasi ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS "Semua orang dapat melihat kampanye" ON kampanye;
CREATE POLICY "Semua orang dapat melihat kampanye"
    ON kampanye FOR SELECT
    TO anon, authenticated
    USING (true);

DROP POLICY IF EXISTS "Pengguna terautentikasi dapat membuat kampanye" ON kampanye;
CREATE POLICY "Pengguna terautentikasi dapat membuat kampanye"
    ON kampanye FOR INSERT
    TO authenticated
    WITH CHECK (auth.uid() = user_id);

DROP POLICY IF EXISTS "Pengguna dapat mengupdate kampanye sendiri" ON kampanye;
CREATE POLICY "Pengguna dapat mengupdate kampanye sendiri"
    ON kampanye FOR UPDATE
    TO authenticated
    USING (auth.uid() = user_id)
    WITH CHECK (auth.uid() = user_id);

DROP POLICY IF EXISTS "Pengguna dapat menghapus kampanye sendiri" ON kampanye;
CREATE POLICY "Pengguna dapat menghapus kampanye sendiri"
    ON kampanye FOR DELETE
    TO authenticated
    USING (auth.uid() = user_id);

DROP POLICY IF EXISTS "Semua orang dapat melihat donasi" ON donasi;
CREATE POLICY "Semua orang dapat melihat donasi"
    ON donasi FOR SELECT
    TO anon, authenticated
    USING (true);

DROP POLICY IF EXISTS "Semua orang dapat berdonasi" ON donasi;
CREATE POLICY "Semua orang dapat berdonasi"
    ON donasi FOR INSERT
    TO anon, authenticated
    WITH CHECK (true);

-- 7. ENABLE REALTIME
ALTER PUBLICATION supabase_realtime ADD TABLE kampanye;
ALTER PUBLICATION supabase_realtime ADD TABLE donasi;

-- 8. DATA AWAL (Contoh Kampanye Nyata Indonesia)
INSERT INTO kampanye (id, user_id, judul, deskripsi, kategori, target_dana, dana_terkumpul, gambar_url, batas_waktu, status)
VALUES
    (
        'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
        NULL,
        'Tanggap Darurat Banjir Bandang Kalimantan',
        'Banjir bandang merendam ratusan rumah warga di Kalimantan. Ribuan keluarga kehilangan tempat tinggal dan membutuhkan bantuan darurat makanan, pakaian bersih, obat-obatan, dan tempat penampungan sementara.',
        'Bencana Alam',
        75000000,
        48500000,
        'https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=800&h=500&fit=crop',
        (CURRENT_DATE + INTERVAL '30 days')::DATE,
        'aktif'
    ),
    (
        'b2c3d4e5-f6a7-8901-bcde-f12345678901',
        NULL,
        'Peduli Gempa: Bangun Hunian Layak Korban Bencana',
        'Gempa bumi merusak pemukiman warga di wilayah pelosok. Mari bersama-sama membangun kembali hunian sementara dan fasilitas sanitasi bagi saudara-saudara kita yang terdampak agar mereka dapat beraktivitas kembali.',
        'Bencana Alam',
        120000000,
        89000000,
        'https://images.unsplash.com/photo-1542810634-71277d95dcbb?w=800&h=500&fit=crop',
        (CURRENT_DATE + INTERVAL '45 days')::DATE,
        'aktif'
    ),
    (
        'c3d4e5f6-a7b8-9012-cdef-123456789012',
        NULL,
        'Beasiswa & Seragam Sekolah Anak Pedalaman NTT',
        'Program bantuan biaya pendidikan, seragam, buku tulis, dan perlengkapan sekolah untuk anak-anak di pelosok Nusa Tenggara Timur agar tetap dapat menuntut ilmu dengan layak.',
        'Pendidikan',
        50000000,
        34200000,
        'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?w=800&h=500&fit=crop',
        (CURRENT_DATE + INTERVAL '60 days')::DATE,
        'aktif'
    ),
    (
        'd4e5f6a7-b8c9-0123-def1-234567890123',
        NULL,
        'Aksi Nyata: Akses Air Bersih & Sanitasi Desa Kekeringan',
        'Pembangunan sumur bor dan instalasi pipa air bersih untuk warga desa di Gunungkidul yang mengalami krisis kekeringan parah setiap musim kemarau.',
        'Air Bersih',
        65000000,
        52100000,
        'https://images.unsplash.com/photo-1541544537156-7627a7a4aa1c?w=800&h=500&fit=crop',
        (CURRENT_DATE + INTERVAL '40 days')::DATE,
        'aktif'
    );

-- Donasi contoh
INSERT INTO donasi (kampanye_id, nama_donatur, email_donatur, jumlah, pesan, metode_pembayaran, status)
VALUES
    ('a1b2c3d4-e5f6-7890-abcd-ef1234567890', 'Rizki Pratama', 'rizki@example.com', 500000, 'Semoga saudara-saudara kita lekas pulih.', 'transfer', 'berhasil'),
    ('a1b2c3d4-e5f6-7890-abcd-ef1234567890', 'Siti Rahmawati', 'siti@example.com', 250000, 'Bismillah, sedikit bantuan dari kami sekeluarga.', 'qris', 'berhasil'),
    ('b2c3d4e5-f6a7-8901-bcde-f12345678901', 'Hamba Allah', 'donatur@gmail.com', 1000000, 'Semoga pembangunan rumah berjalan lancar.', 'transfer', 'berhasil'),
    ('c3d4e5f6-a7b8-9012-cdef-123456789012', 'Budi Santoso', 'budi.s@yahoo.com', 350000, 'Semangat belajar untuk adik-adik di NTT!', 'transfer', 'berhasil'),
    ('d4e5f6a7-b8c9-0123-def1-234567890123', 'Dewi Lestari', 'dewi@gmail.com', 750000, 'Semoga segera mengalir air bersih yang berkah.', 'qris', 'berhasil');
