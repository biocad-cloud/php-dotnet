Imports System.IO
Imports Microsoft.VisualBasic.CommandLine
Imports Microsoft.VisualBasic.CommandLine.Reflection
Imports Microsoft.VisualBasic.Text
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

        ' 如果选择utf8编码的话，在windows平台上面utf8是默认带有BOM头的
        ' 这个编码会导致php脚本的解析不正常
        ' 在这里使用不带有BOM头信息的utf8编码
        Using out As StreamWriter = args.OpenStreamOutput("/out", Encodings.UTF8WithoutBOM)
            Call out.WriteLine(mysqlDoc.GenerateCode())
            Call out.Flush()
        End Using

        Return 0
    End Function
End Module
