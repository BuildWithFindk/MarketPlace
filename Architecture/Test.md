``` 
npx autocannon -d 30 http://127.0.0.1:8000
```
### So your testing chain is basically:
Autocannon
    ↓
127.0.0.1:8000
    ↓
Laravel PHP server
    ↓
Laravel application
    ↓
HTML response

 ## production capacity estimate workflow
 Internet
   ↓
Load Balancer / Nginx
   ↓
PHP-FPM
   ↓
Laravel
   ↓
Redis / Database / Storage



### current local setup
MacBook Pro
│
├── Laravel
│   └── php artisan serve
│       └── 127.0.0.1:8000
│
└── Load generator
    └── Autocannon
        ├── 10 connections
        ├── 50 connections
        ├── 100 connections
        └── 500 connections