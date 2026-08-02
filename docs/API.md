# Dokumentasi REST API V1 PHP-PDNSManager Enterprise Edition

**PHP-PDNSManager Enterprise Edition** menyediakan antarmuka **RESTful API V1** yang lengkap untuk memungkinkan otomatisasi pengelolaan DNS melalui skrip eksternal, sistem CI/CD, Terraform provider, atau alat infrastruktur lainnya.

---

## 🔑 Autentikasi API

Setiap permintaan ke REST API wajib menyertakan token autentikasi **Bearer JWT** atau **API Key** pada header HTTP `Authorization`:

```http
Authorization: Bearer <TOKEN_JWT_ANDA>
```

Atau menggunakan Header API Key khusus:

```http
X-API-Key: <USER_API_KEY_ANDA>
```

---

## 📡 Format Respon Standar JSON

Seluruh respon API dikembalikan dalam format **JSON** dengan header `Content-Type: application/json`.

### Respon Sukses (200 OK / 201 Created)

```json
{
    "status": "success",
    "data": {
        "id": "example.com.",
        "name": "example.com.",
        "type": "Zone",
        "kind": "Native",
        "serial": 2026080201,
        "dnssec": true
    },
    "message": "Resource retrieved successfully"
}
```

### Format Respon Error (RFC 7807 Problem Details)

Saat terjadi kegagalan, API mengembalikan respon error sesuai standar RFC 7807:

```json
{
    "type": "https://pdns.domainanda.com/docs/errors/NOT_FOUND",
    "title": "Resource Not Found",
    "status": 404,
    "detail": "DNS Zone 'nonexistent.com.' was not found on the PowerDNS server.",
    "instance": "/api/v1/zones/nonexistent.com."
}
```

---

## 📚 Daftar Endpoint REST API V1

### 🌐 1. Manajemen Zone (`/api/v1/zones`)

#### A. Dapatkan Daftar Zone

- **HTTP Method**: `GET`
- **Path**: `/api/v1/zones`
- **Query Parameters**:
    - `search` (string, opsional): Filter berdasarkan nama zone.
- **Contoh Request**:

```bash
curl -X GET "https://pdns.domainanda.com/api/v1/zones?search=example" \
     -H "Authorization: Bearer <YOUR_JWT_TOKEN>" \
     -H "Accept: application/json"
```

#### B. Buat Zone Baru

- **HTTP Method**: `POST`
- **Path**: `/api/v1/zones`
- **Request Body**:

```json
{
    "name": "example.com.",
    "kind": "Native",
    "nameservers": ["ns1.example.com.", "ns2.example.com."]
}
```

#### C. Dapatkan Detail Zone

- **HTTP Method**: `GET`
- **Path**: `/api/v1/zones/{zone_id}`

#### D. Hapus Zone

- **HTTP Method**: `DELETE`
- **Path**: `/api/v1/zones/{zone_id}`

---

### ⚡ 2. Manajemen Record (`/api/v1/records`)

#### A. Dapatkan Daftar Record dalam Zone

- **HTTP Method**: `GET`
- **Path**: `/api/v1/zones/{zone_id}/records`

#### B. Tambah / Perbarui RRSet Record

- **HTTP Method**: `PATCH`
- **Path**: `/api/v1/zones/{zone_id}/records`
- **Request Body**:

```json
{
    "rrsets": [
        {
            "name": "app.example.com.",
            "type": "A",
            "ttl": 3600,
            "changetype": "REPLACE",
            "records": [
                {
                    "content": "192.0.2.1",
                    "disabled": false
                }
            ]
        }
    ]
}
```

#### C. Hapus Record

- **HTTP Method**: `DELETE`
- **Path**: `/api/v1/zones/{zone_id}/records/{name}/{type}`

---

### 🔐 3. Manajemen DNSSEC Cryptokeys (`/api/v1/dnssec`)

#### A. Dapatkan Daftar Cryptokeys & Record DS

- **HTTP Method**: `GET`
- **Path**: `/api/v1/zones/{zone_id}/cryptokeys`

#### B. Aktifkan DNSSEC (Generate Keys)

- **HTTP Method**: `POST`
- **Path**: `/api/v1/zones/{zone_id}/cryptokeys`
- **Request Body**:

```json
{
    "keytype": "ksk",
    "bits": 256,
    "algo": "ecdsa256",
    "active": true
}
```

#### C. Hapus Cryptokey

- **HTTP Method**: `DELETE`
- **Path**: `/api/v1/zones/{zone_id}/cryptokeys/{key_id}`

---

## 🚦 Kode Status HTTP (HTTP Status Codes)

| Kode  | Nama              | Deskripsi                                                |
| :---- | :---------------- | :------------------------------------------------------- |
| `200` | OK                | Permintaan berhasil diproses.                            |
| `201` | Created           | Resource baru (Zone/Record) berhasil dibuat.             |
| `400` | Bad Request       | Parameter masukan tidak valid atau sintaks JSON salah.   |
| `401` | Unauthorized      | Token autentikasi hilang atau tidak valid.               |
| `403` | Forbidden         | Pengguna tidak memiliki hak akses (RBAC) untuk aksi ini. |
| `404` | Not Found         | Zone, Record, atau Endpoint tidak ditemukan.             |
| `429` | Too Many Requests | Melebihi batas ambang _Rate Limit_.                      |
| `500` | Internal Error    | Kegagalan server internal atau masalah koneksi PowerDNS. |
