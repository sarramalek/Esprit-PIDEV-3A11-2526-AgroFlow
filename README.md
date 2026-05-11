Esprit-PIDEV-3A11-2526-AgroFlow

Overview

This project was developed as part of the PIDEV – 3rd Year Engineering Program at Esprit School of Engineering (Academic Year 2025–2026).
It consists of a full-stack web application that enables agricultural cooperatives and agribusinesses to manage farming operations, track field activities, and facilitate collaboration between administrators, agricultural experts, and field employees.

Features

User Module - Sarra Malek  : 

Two-Factor Authentication (2FA) – Enhanced account security requiring a second verification step during login 

Speech-to-Text – Voice input support allowing users to interact with the platform via spoken commands

Text-to-Speech – Audio output functionality that reads content aloud for improved accessibility

Employee Repartition by Terrain – Visual mapping and assignment of employees to specific fields based on workload and disponibility . 

Role-Based Access Control – Three distinct roles: Super Admin, Agriculture, and Employee

User Management Dashboard – CRUD operations for users, role assignment, and profile 
management

Subscription & Offers Management – Farmers can subscribe to one or multiple subscription plans simultaneously, with flexible offer management for different service tiers

Alert System – Automated notifications and alerts for important events, task reminders, subscription renewals, and critical updates

 Terrain & Rotation Module - Nourane Landoulsi :
- Terrain CRUD – Full management of agricultural plots (name, location, surface area, soil type, pH level) for both Admin and Farmer roles
- Crop Rotation CRUD – Manage crop rotations linked to terrains and plants with start/end dates and active/inactive status
- Plant CRUD – Manage plant species with variety, water needs (L/day), and growth cycle (days)
- Property Certificate (PDF) – Generate a downloadable ownership certificate for each terrain
- PDF Export – Export the full rotation list as a formatted PDF with AgroFlow branding
- KPI Dashboard – Real-time statistics: total terrains, total surface area, average soil pH, rotation counts and average duration
- Multi-criteria Search & Filtering – Filter terrains by name, location, soil type; filter rotations by status and keyword
- Column Sorting & Pagination – Sort any table column and navigate results with smart pagination
- Live Weather Modal (Open-Meteo API) – Real-time temperature, humidity, wind speed and precipitation for any terrain location, with farming recommendations
- Dynamic Geolocation (Nominatim / OpenStreetMap API) – Converts terrain location names into GPS coordinates dynamically
- Interactive Map Modal (Leaflet.js + OpenStreetMap) – Displays each terrain on a fully interactive map with a custom marker and GPS coordinates
- Air Quality Modal (Open-Meteo Air Quality API) – European AQI index, PM10, PM2.5, NO₂, ozone and dust levels with color-coded farming alerts
- Soil Analysis Algorithm – Scores each terrain out of 100 based on pH, surface area and soil type, with personalized plant recommendations and irrigation advice
- Smart Irrigation Advisor – Computes the net adjusted water requirement per plant by combining base water needs with live weather data (temperature, humidity, precipitation, wind)
- Best Rotation Suggestion Algorithm – Scores every rotation out of 100 using 5 weighted criteria (soil pH, active status, rotation duration, water needs vs. live weather, soil type) and displays the top 3 with detailed reasoning

Tech Stack
- Open-Meteo API – Live weather & air quality data (no API key required)
- Nominatim (OpenStreetMap) – Dynamic geocoding (no API key required)
- Leaflet.js + OpenStreetMap – Interactive mapping (no API key required)

Frontend

Twig – Symfony's templating engine for dynamic views
Bootstrap – Responsive CSS framework
JavaScript – Client-side interactivity
HTML5 / CSS3 – Standard web technologies
Backend

PHP 8.x – Core programming language
Symfony 6.x – Full-stack web framework (MVC architecture)
Doctrine ORM – Database management and entity mapping
MySQL – Relational database
Composer – PHP dependency manager

Architecture

AgroFlow follows the MVC (Model-View-Controller) architecture provided by Symfony. The application is organized into domain-specific modules with role-based security enforced through Symfony's security component.

Contributors

- Sarra Malek – User Module (2FA, Accessibility, Employee-Terrain Repartition, Subscription & Offers, Alert System)
- Nourane Landoulsi – Terrain & Rotation Module (CRUD, AI Algorithms, External APIs, AgroBot, PDF Export, Maps, Weather, Air Quality)

Academic Context

Developed at Esprit School of Engineering – Tunisia
PIDEV – 3A | 2025–2026

Getting Started

Prerequisites

PHP 8.0 or higher
Composer
MySQL
Symfony CLI (recommended)
Docker (optional) 

Acknowledgment

We would like to express our sincere gratitude to our instructors and supervisors at Esprit School of Engineering for their guidance, support, and valuable feedback throughout the development of this project. We also thank our team members for their collaboration and dedication .
