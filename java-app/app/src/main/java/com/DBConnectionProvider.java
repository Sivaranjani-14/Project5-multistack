package com.siva.multispot;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public class DBConnectionProvider {
    
    public static Connection getConnection() throws SQLException, ClassNotFoundException {
        String host = System.getenv("RDS_HOSTNAME");
        String dbName = System.getenv("RDS_DB_NAME");
        String user = System.getenv("RDS_USERNAME");
        String password = System.getenv("RDS_PASSWORD");
        String port = System.getenv("RDS_PORT");

        if (dbName == null) dbName = "phpdb"; 
        if (port == null) port = "3306";

        String jdbcUrl = "jdbc:mysql://" + host + ":" + port + "/" + dbName + "?useSSL=false&serverTimezone=UTC&allowPublicKeyRetrieval=true";
        
        Class.forName("com.mysql.cj.jdbc.Driver");
        return DriverManager.getConnection(jdbcUrl, user, password);
    }
}