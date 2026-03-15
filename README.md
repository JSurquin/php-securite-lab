# Lab Application – Sécurité PHP

> ⚠️ **USAGE PÉDAGOGIQUE UNIQUEMENT**  
> Cette application contient des failles de sécurité **intentionnelles**.  
> Ne JAMAIS déployer en production ou sur un réseau public.

## Démarrage rapide

```bash
git clone https://github.com/jimmylansrq/php-securite-lab
cd php-securite-lab
docker compose up -d
```

| Service | URL |
|---------|-----|
| Application | http://localhost:8080 |
| PhpMyAdmin | http://localhost:8081 |

## Comptes de test

| Utilisateur | Mot de passe | Rôle |
|-------------|--------------|------|
| `admin` | `admin123` | admin |
| `alice` | `password` | user |
| `bob` | `bob2024` | user |

## Pages et failles associées

| Page | Failles |
|------|---------|
| `/login.php` | SQLi-01, CSRF-01, SESSION-02 |
| `/register.php` | PASSWD-01, VALIDATION-01 |
| `/article.php?id=1` | SQLi-02, XSS-01, XSS-02, CSRF-02 |
| `/search.php` | SQLi-03, XSS-03 |
| `/upload.php` | UPLOAD-01 à 04, CSRF-03 |
| `/contact.php` | XSS-04, CSRF-04, IDOR-01 |
| `/dashboard.php` | IDOR-02, CSRF-05, PASSWD-02, SESSION-03 |
| `/page.php?p=home` | LFI-01 |
| `/exec.php` | CMD-01 |
| `/config.php` | CONFIG-01, SESSION-01 |

## Exercices

Consulte [CORRECTIONS.md](./CORRECTIONS.md) pour :
- La description de chaque faille
- Les payloads d'exploitation
- Le code corrigé commenté
- L'ordre recommandé pour progresser

## Structure du projet

```
app/
├── docker-compose.yml       # Environnement LAMP (PHP 8.2 + MySQL 8 + PhpMyAdmin)
├── README.md                # Ce fichier
├── CORRECTIONS.md           # Guide pédagogique avec toutes les corrections
├── corrections/             # Fichiers PHP corrigés (à comparer avec public/)
│   ├── config.php           # SESSION-01, CONFIG-01
│   ├── login.php            # SQLi-01, SESSION-02, CSRF-01, PASSWD-02
│   ├── register.php         # PASSWD-01, VALIDATION-01
│   ├── article.php          # SQLi-02, XSS-01/02, CSRF-02
│   ├── search.php           # SQLi-03, XSS-03
│   ├── dashboard.php        # IDOR-02, CSRF-05, PASSWD-02
│   ├── exec.php             # CMD-01
│   ├── page.php             # LFI-01
│   └── contact.php          # IDOR-01, CSRF-04, XSS-04
├── sql/
│   └── init.sql             # Schéma et données de test
└── public/                  # Web-root Apache
    ├── config.php            # Configuration (failles CONFIG, SESSION)
    ├── index.php             # Page d'accueil
    ├── login.php             # Connexion (SQLi-01, CSRF-01, SESSION-02)
    ├── register.php          # Inscription (PASSWD-01, VALIDATION-01)
    ├── logout.php            # Déconnexion
    ├── article.php           # Article + commentaires (SQLi-02, XSS-01/02, CSRF-02)
    ├── search.php            # Recherche (SQLi-03, XSS-03)
    ├── upload.php            # Upload de fichiers (UPLOAD-01 à 04, CSRF-03)
    ├── contact.php           # Formulaire contact (XSS-04, CSRF-04, IDOR-01)
    ├── dashboard.php         # Profil utilisateur (IDOR-02, CSRF-05, PASSWD-02)
    ├── page.php              # Inclusion dynamique (LFI-01)
    ├── home.php              # Page incluse par page.php
    ├── exec.php              # Outil ping (CMD-01)
    └── uploads/              # ⚠️ Dossier d'upload dans le web-root (faille intentionnelle)
```
