# TechFit Web Application

[![Stability](https://img.shields.io/badge/stability-stable-green.svg)](YOUR_LICENSE_OR_REPO_LINK_HERE) 

## 🌟 Introduction

TechFit is an innovative online skill assessment platform designed to revolutionize the hiring process for both IT job seekers and employers. Its primary objective is to provide a structured, multi-stage evaluation system that allows IT professionals to showcase their technical skills, experience, and unique strengths. Concurrently, it streamlines the recruitment process for employers by offering a curated pool of highly skilled candidates.

The platform goes beyond traditional resumes and interviews by incorporating targeted questions, coding challenges, personality tests, and work-style assessments, creating a well-rounded and dynamic candidate profile. TechFit's scope covers key hiring steps, from user registration and comprehensive skill assessment to efficient candidate shortlisting.

By bridging the gap between opportunity and expertise, TechFit aims to create a more efficient, personalized, and effective hiring ecosystem for all stakeholders in the IT fields.

---

## 🚀 Getting Started

To run the TechFit web application locally, you will need to have **XAMPP** (or a similar local web server environment with PHP and MySQL) installed and running on your computer.

### Prerequisites

* **XAMPP:** Download and install XAMPP from [Apache Friends](https://www.apachefriends.org/index.html). Ensure Apache and MySQL services are running.

### Installation Steps

Follow these steps to set up TechFit on your local machine:

1.  **Extract Project Files:**
    * Download and extract the project `.zip` file. This will typically create two items: a folder named `Techfit` (containing the web application files) and a database file named `techfit.sql`.
    * Alternatively, you can clone the repository using Git:
        ```bash
        git clone https://github.com/KhongCL/Techfit.git
        ```

2.  **Import Database:**
    * Open your web browser and navigate to `http://localhost/phpmyadmin`.
    * In phpMyAdmin, click on the "Databases" tab or select "New" from the left sidebar to create a new database.
    * **Create a new database named `techfit`**.
    * Select the newly created `techfit` database from the left sidebar.
    * Go to the "Import" tab.
    * Click "Choose File" and select the `techfit.sql` file from your extracted project.
    * Scroll down and click the "Import" button to import the database schema and data.

3.  **Copy Website Folder:**
    * Copy the entire `Techfit` directory (the one containing your `index.php`, `admin_login.php`, etc.)
    * Paste it into your XAMPP's web server document root, typically:
        * `C:\xampp\htdocs\` (for Windows)
        * `/Applications/XAMPP/htdocs/` (for macOS)
        * `/opt/lampp/htdocs/` (for Linux)

4.  **Access the Application:**
    * Ensure that **Apache** and **MySQL** services are started in your XAMPP Control Panel.
    * Open your web browser and navigate to the following URLs:
        * **General Website (Job Seeker/Employer Login/Register):**
            `http://localhost/Techfit/`
        * **Admin Login:**
            `http://localhost/Techfit/admin_login.php?key=techfit`
        * **Admin Register:**
            `http://localhost/Techfit/admin_register.php?key=techfit`

---

## 💻 Technologies Used

* **Frontend:** HTML, CSS, JavaScript
* **Backend:** PHP
* **Database:** MySQL (managed via phpMyAdmin)
* **Local Server Environment:** XAMPP

---

## ✨ Features and Functionalities

### For Job Seekers:
* Secure registration and login.
* Create and manage detailed profiles, including resume uploads and LinkedIn links.
* Take multi-stage assessments: preliminary questions, scenario-based questions, programming challenges (Python, JavaScript, Java, C++), and work-style assessments.
* Review comprehensive assessment history and performance summaries.
* Showcase proficiency in various programming languages.
* Share work-life balance and business culture preferences.

### For Employers:
* Secure registration and login.
* Access a single point of entry to a pool of skilled candidates.
* Efficiently search and filter candidate profiles based on technical requirements, personality traits, and workplace culture.
* Browse detailed candidate profiles and review assessment results.
* Utilize an "Interested/Not Interested" feature for easy candidate shortlisting.
* Make data-driven hiring decisions based on objective skill validation.

### For Administrators:
* Secure login and access to a dedicated admin portal.
* Create, edit, delete, and restore assessments (including setting names, descriptions, and choices).
* Add, edit, delete, and restore questions within assessments.
* Manage user records (job seekers and employers).
* Oversee system settings, resources, and reports to maintain an effective environment.

---

### 📸 Application Screenshots

#### **Job Seeker Complete Assessment**
<img alt="Job Seeker Dashboard" src="https://github.com/user-attachments/assets/57b3547a-2367-4d3a-a5f2-bae732f541ef" />

#### **Job Seeker Assessment Result**
<img width="1920" height="1503" alt="Techfit Assessment Result" src="https://github.com/user-attachments/assets/6ae6f7f0-92b9-4487-a5ef-801f86e43d25" />

#### **Employer Search Candidates**
<img alt="Employer Dashboard" src="https://github.com/user-attachments/assets/5a1011ae-497e-431c-8784-beb8168f77e3" />

#### **Admin Manage Assessents**
<img alt="Admin Panel Example" src="https://github.com/user-attachments/assets/5fa08057-d0d8-46d8-b6f2-833997f5eaf0" />

---

## 🛡️ Security Considerations

* **Password Hashing:** Sensitive user data like passwords are encrypted.
* **Admin Access:** The Admin portal is restricted to a special link with a unique key.
* **Assessment Integrity:** Measures like questions with concrete answers and time tracking will be necessary to maintain credibility.

---

## 📈 Scalability & Performance

* Designed to support multiple concurrent users.
* Assessments aim to load within 2 seconds, with instant processing of results.
* Cross-device compatibility ensures adaptive functionality across desktops and mobile devices.

---

## 📄 Documentation

For more in-depth information regarding TechFit, its design, background analysis, requirements, and more, please refer to the project's official documentation.

---

## 🤝 Contributing

We welcome contributions! If you'd like to contribute to TechFit, please fork the repository and submit a pull request with your changes.

---

## 📞 Support

If you encounter any issues or have questions, please open an issue on this GitHub repository.

---

**Thank you for using TechFit!**

**Note:** Please remember to update the placeholder link for the "Stability" badge at the very top: `YOUR_LICENSE_OR_REPO_LINK_HERE`.
