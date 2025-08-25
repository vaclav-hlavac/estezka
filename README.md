# E-stezka

**E-stezka** is a modern digital tool for the Czech Scouting "stezka" (personal development path).  
It allows scouts to clearly track their progress, complete tasks, and keep records of achievements – directly on their mobile device.  
Leaders can easily review, approve, and manage members of their troop or patrol.

---

## 🔧 API (Server)

This repository contains the **server application** written in PHP (Slim framework).  
It provides a REST API for managing E-stezka data.

- **API Documentation**: see the [OpenAPI specification](./src/OpenApiSpec.php)  

---

## 📱 Mobile Application

The companion mobile application (Flutter), originally created as a master’s thesis at CTU FIT,  
is designed with simplicity in mind while keeping the concepts of the paper version of the scouting path.

- Scouts can complete tasks and request approvals.  
- Leaders can review and confirm progress.  
- The application is currently in the testing phase.  
- The codebase will be available in a separate repository: [estezka-app](https://github.com/your-org/estezka-app) *TODO*

---

## 🚀 Getting Started (Local Development)

1. Clone the repository:
   ```bash
   git clone https://github.com/vaclav-hlavac/estezka.git
   cd estezka
   
2. Install dependencies:

  composer install

3. Copy the environment configuration:
  cp .env.example .env
  Adjust values for database connection, JWT secret, etc.

4. Run the local server (using PHP built-in):
  php -S localhost:8080 -t public

5. The API is now available at:
  http://localhost:8080

🧪 Testing
Run unit, integration, and functional tests with:
vendor/bin/phpunit
Tests use a separate database configuration defined in .env.testing.

🤝 Contributing
Contributions are welcome!
Please open an issue or submit a pull request.

📄 License
Author: Václav Hlaváč  
Title: E-Stezka – Mobile application for the scouting organization  

This work (including all source code and documentation) is licensed under a **non-exclusive license** in accordance with Section 2373(2) of Act No. 89/2012 Coll., the Civil Code of the Czech Republic.

- The license grants permission to all persons to use the Work in any way that does not diminish its value.  
- The license is **limited to non-commercial use only**.  
- This permission is unlimited in time, territory, and quantity.  
- Any **commercial use** requires the **prior express consent of the author**.


---
