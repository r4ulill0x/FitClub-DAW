# 🏋️ FitClub – Platformă Management Fitness  
### ⚡ Aplicație Web - Prroiect DAW

**FitClub** este o aplicație web completă pentru managementul unei săli de fitness moderne, dezvoltată în **PHP & MySQL**, cu accent pe **securitate**, **UX fluid** și **raportare eficientă**.  
Proiectul utilizează un design **futuristic (Cyber / Dark Mode)** și o arhitectură clară, potrivită pentru aplicații reale.

---

## 🚀 Funcționalități Principale

✔️ **Arhitectură Robustă**  
- Backend dezvoltat în PHP 8.x  
- Bază de date relațională MySQL  

✔️ **Operații CRUD Complete**  
- Gestionare membri  
- Clase & antrenamente  
- Rezervări  

✔️ **Sistem de Autentificare & Securitate**  
- Login & Register  
- Hashing parole (`password_hash`)  
- Management sesiuni & Logout securizat  

✔️ **Roluri Utilizatori**  
- 🛡️ Administrator  
- 🏋️ Antrenor  
- 👤 Membru  
- Permisiuni și interfețe diferite pentru fiecare rol  

✔️ **Navigație Dinamică**  
- Dashboard personalizat  
- Profil utilizator  
- Orar și management activități  

✔️ **Comunicare SMTP**  
- Formular de contact funcțional  
- Trimitere email în timp real  

✔️ **Design Responsive**  
- Desktop, Tabletă, Smartphone  

---

## 🛠️ Tehnologii Utilizate

### 🔧 Backend
- **PHP 8.x**
- **PDO** pentru conexiuni sigure la baza de date
- Protecție împotriva **SQL Injection**

### 🗄️ Bază de Date
- **MySQL**
- Structură relațională optimizată

### 🎨 Frontend
- **HTML5**
- **CSS3**
  - CSS Custom Variables (temă Cyber/Dark)
  - Flexbox & Grid
  - Design responsive

---

## 🔐 Securitate

- Hashing parole (`password_hash`, `password_verify`)
- Sesiuni PHP securizate
- Separare logică între roluri
- Protecție SQL Injection (PDO + prepared statements)

---

## 📱 Design Responsiv

| Dispozitiv | Experiență |
|-----------|-----------|
| 🖥️ Desktop | Dashboard complet, tabele avansate |
| 📱 Tabletă | Layout adaptiv |
| 📲 Mobil | Meniu vertical, elemente touch-friendly |

---

## 🔧 Instalare și Configurare

### 1️⃣ Clonează repository-ul
```bash
git clone https://github.com/utilizator/fitclub.git
