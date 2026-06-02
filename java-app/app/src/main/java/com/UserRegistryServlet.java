package com.siva.multispot;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;

import java.io.IOException;
import java.io.PrintWriter;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.Statement;

@WebServlet("/")
public class UserRegistryServlet extends HttpServlet {

    @Override
    public void init() throws ServletException {
        // Automatically set up the isolated student table on the shared database schema
        try (Connection conn = DBConnectionProvider.getConnection();
             Statement stmt = conn.createStatement()) {
            String createTableSQL = "CREATE TABLE IF NOT EXISTS students (" +
                    "id INT AUTO_INCREMENT PRIMARY KEY, " +
                    "student_name VARCHAR(100) NOT NULL, " +
                    "course VARCHAR(100) NOT NULL, " +
                    "enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP" +
                    ")";
            stmt.execute(createTableSQL);
        } catch (Exception e) {
            getServletContext().log("Failed to initialize student table: " + e.getMessage(), e);
        }
    }

    @Override
    protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        response.setContentType("text/html;charset=UTF-8");
        PrintWriter out = response.getWriter();
        
        String message = (String) request.getAttribute("message");
        String statusClass = (String) request.getAttribute("statusClass");
        if (message == null) message = "";
        if (statusClass == null) statusClass = "";

        out.println("<!DOCTYPE html>");
        out.println("<html lang='en'>");
        out.println("<head>");
        out.println("    <meta charset='UTF-8'>");
        out.println("    <meta name='viewport' content='width=device-width, initial-scale=1.0'>");
        out.println("    <title>Java Student Management </title>");
        out.println("    <style>");
        out.println("        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f0f4f8; color: #333; margin: 0; padding: 40px; }");
        out.println("        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-top: 4px solid #2563eb; }");
        out.println("        h2 { color: #1e3a8a; margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }");
        out.println("        h3 { color: #334155; margin-top: 30px; }");
        out.println("        .alert { padding: 12px 16px; margin-bottom: 20px; border-radius: 4px; font-weight: 500; }");
        out.println("        .success { background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }");
        out.println("        .error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }");
        out.println("        .form-group { margin-bottom: 15px; }");
        out.println("        label { display: block; margin-bottom: 6px; font-weight: 600; color: #475569; }");
        out.println("        input[type='text'] { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; font-size: 14px; }");
        out.println("        button { background-color: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; font-weight: 600; cursor: pointer; font-size: 14px; }");
        out.println("        button:hover { background-color: #1d4ed8; }");
        out.println("        table { width: 100%; border-collapse: collapse; margin-top: 15px; }");
        out.println("        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }");
        out.println("        th { background-color: #f1f5f9; font-weight: 600; color: #475569; }");
        out.println("    </style>");
        out.println("</head>");
        out.println("<body>");
        out.println("<div class='container'>");
        out.println("    <h2>Java Stack: Student Enrollment System</h2>");
        
        if (!message.isEmpty()) {
            out.println("    <div class='alert " + statusClass + "'>" + message + "</div>");
        }

        out.println("    <form method='POST' action='./'>");
        out.println("        <div class='form-group'>");
        out.println("            <label for='student_name'>Student Full Name</label>");
        out.println("            <input type='text' id='student_name' name='student_name' placeholder='e.g. Sunil Kumar' required>");
        out.println("        </div>");
        out.println("        <div class='form-group'>");
        out.println("            <label for='course'>Enrolled Course</label>");
        out.println("            <input type='text' id='course' name='course' placeholder='e.g. AWS Cloud Bootcamp' required>");
        out.println("        </div>");
        out.println("        <button type='submit'>Enroll Student</button>");
        out.println("    </form>");
        out.println("    <h3>Enrolled Students Roster (Isolated Table)</h3>");
        out.println("    <table>");
        out.println("        <thead><tr><th>ID</th><th>Student Name</th><th>Course</th><th>Enrollment Date</th></tr></thead>");
        out.println("        <tbody>");

        try (Connection conn = DBConnectionProvider.getConnection();
             Statement stmt = conn.createStatement();
             ResultSet rs = stmt.executeQuery("SELECT id, student_name, course, enrolled_at FROM students ORDER BY id DESC")) {
            
            boolean hasRecords = false;
            while (rs.next()) {
                hasRecords = true;
                out.println("        <tr>");
                out.println("            <td>" + rs.getInt("id") + "</td>");
                out.println("            <td><strong>" + rs.getString("student_name") + "</strong></td>");
                out.println("            <td>" + rs.getString("course") + "</td>");
                out.println("            <td>" + rs.getTimestamp("enrolled_at") + "</td>");
                out.println("        </tr>");
            }
            if (!hasRecords) {
                out.println("        <tr><td colspan='4' style='text-align:center; color:#94a3b8; font-style:italic;'>No students enrolled yet.</td></tr>");
            }
        } catch (Exception e) {
            out.println("        <tr><td colspan='4' class='alert error'>JDBC Connection Error: " + e.getMessage() + "</td></tr>");
        }

        out.println("        </tbody>");
        out.println("    </table>");
        out.println("</div>");
        out.println("</body>");
        out.println("</html>");
    }

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
        String studentName = request.getParameter("student_name");
        String course = request.getParameter("course");

        if (studentName != null && course != null && !studentName.trim().isEmpty() && !course.trim().isEmpty()) {
            try (Connection conn = DBConnectionProvider.getConnection();
                 PreparedStatement ps = conn.prepareStatement("INSERT INTO students (student_name, course) VALUES (?, ?)")) {
                
                ps.setString(1, studentName.trim());
                ps.setString(2, course.trim());
                ps.executeUpdate();

                request.setAttribute("message", "Student enrolled successfully into Tomcat cluster database!");
                request.setAttribute("statusClass", "success");
            } catch (Exception e) {
                request.setAttribute("message", "Database Error: " + e.getMessage());
                request.setAttribute("statusClass", "error");
            }
        } else {
            request.setAttribute("message", "Validation Error: Inputs cannot be empty.");
            request.setAttribute("statusClass", "error");
        }
        doGet(request, response);
    }
}