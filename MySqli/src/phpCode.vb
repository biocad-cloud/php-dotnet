Imports System.IO
Imports Microsoft.VisualBasic.CommandLine
Imports Microsoft.VisualBasic.CommandLine.Reflection
Imports Oracle.LinuxCompatibility.MySQL.CodeSolution.PHP

Public Module phpCode

    Sub New()
        VBDebugger.ForceSTDError = True
    End Sub

    Public Function Main() As Integer
        Return GetType(phpCode).RunCLI(App.CommandLine)
    End Function

    <ExportAPI("/php")>
    <Usage("/php /doc <structure.sql> [/out <mysqli.class.php>]")>
    Public Function BuildSchemaCache(args As CommandLine) As Integer
        Dim mysqlDoc As StreamReader = args.OpenStreamInput("/doc")

        Using out As StreamWriter = args.OpenStreamOutput("/out")
            Call out.WriteLine(mysqlDoc.GenerateCode())
            Call out.Flush()
        End Using

        Return 0
    End Function
End Module
