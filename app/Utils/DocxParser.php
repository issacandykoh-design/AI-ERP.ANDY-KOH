<?php

namespace App\Utils;

class DocxParser
{
    /**
     * 从DOCX文件中提取文本内容
     * 
     * @param string $filePath DOCX文件路径
     * @return string 提取的文本内容
     */
    public static function extractText($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("文件不存在: {$filePath}");
        }

        // 检查是否为有效的ZIP文件（DOCX本质上是ZIP文件）
        $zip = new \ZipArchive();
        $result = $zip->open($filePath);
        
        if ($result !== TRUE) {
            throw new \Exception("无法打开DOCX文件: {$filePath}");
        }

        // 提取document.xml文件内容
        $documentXml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($documentXml === false) {
            throw new \Exception("无法从DOCX文件中提取document.xml");
        }

        // 解析XML并提取文本
        return self::parseDocumentXml($documentXml);
    }

    /**
     * 解析document.xml并提取文本内容
     * 
     * @param string $xmlContent XML内容
     * @return string 提取的文本
     */
    private static function parseDocumentXml($xmlContent)
    {
        // 禁用libxml错误报告
        $useInternalErrors = libxml_use_internal_errors(true);
        
        try {
            // 创建DOMDocument对象
            $dom = new \DOMDocument();
            $dom->loadXML($xmlContent);

            // 获取所有文本节点
            $xpath = new \DOMXPath($dom);
            $textNodes = $xpath->query('//w:t');

            $text = '';
            foreach ($textNodes as $node) {
                $text .= $node->nodeValue;
            }

            // 清理文本：移除多余的空白字符
            $text = preg_replace('/\s+/', ' ', $text);
            $text = trim($text);

            return $text;

        } catch (\Exception $e) {
            // 如果XML解析失败，尝试使用正则表达式提取
            return self::extractTextWithRegex($xmlContent);
        } finally {
            // 恢复libxml错误报告设置
            libxml_use_internal_errors($useInternalErrors);
        }
    }

    /**
     * 使用正则表达式从XML中提取文本（备用方法）
     * 
     * @param string $xmlContent XML内容
     * @return string 提取的文本
     */
    private static function extractTextWithRegex($xmlContent)
    {
        // 匹配所有<w:t>标签中的内容
        preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/s', $xmlContent, $matches);
        
        $text = '';
        if (!empty($matches[1])) {
            foreach ($matches[1] as $match) {
                // 解码XML实体
                $decodedText = html_entity_decode($match, ENT_XML1, 'UTF-8');
                $text .= $decodedText;
            }
        }

        // 清理文本
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        return $text;
    }

    /**
     * 检查文件是否为有效的DOCX文件
     * 
     * @param string $filePath 文件路径
     * @return bool
     */
    public static function isValidDocx($filePath)
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $zip = new \ZipArchive();
        $result = $zip->open($filePath);
        
        if ($result !== TRUE) {
            return false;
        }

        // 检查是否包含必要的DOCX文件结构
        $hasContentTypes = $zip->getFromName('[Content_Types].xml') !== false;
        $hasDocument = $zip->getFromName('word/document.xml') !== false;
        
        $zip->close();

        return $hasContentTypes && $hasDocument;
    }
}