Imports System.IO
Imports Oracle.LinuxCompatibility.MySQL.CodeSolution
Imports Oracle.LinuxCompatibility.MySQL.Reflection.Schema

Public Module phpCode

    Public Function GenerateCode(mysqlDoc As StreamReader) As String
        Dim tables As Table() = mysqlDoc.LoadSQLDoc

    End Function
End Module
