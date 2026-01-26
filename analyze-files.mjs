import fs from 'fs/promises';
import path from 'path';
import pdf from 'pdf-parse';
import mammoth from 'mammoth';
import { GoogleGenerativeAI } from "@google/generative-ai";

// Initialize Gemini (Get an API key from Google AI Studio)
const genAI = new GoogleGenerativeAI("YOUR_GEMINI_API_KEY");
const model = genAI.getGenerativeModel({ model: "gemini-1.5-flash" });

export async function summarizeFile(relativeFilePath) {
    const fullPath = path.join('/home/site/wwwroot', relativeFilePath);
    const ext = path.extname(fullPath).toLowerCase();
    
    try {
        // --- CASE 1: IMAGE (Screenshots) ---
        if (['.png', '.jpg', '.jpeg', '.webp'].includes(ext)) {
            const imageData = await fs.readFile(fullPath);
            const part = {
                inlineData: { data: imageData.toString("base64"), mimeType: `image/${ext.slice(1)}` }
            };
            const result = await model.generateContent(["Describe this screenshot and summarize any issues shown:", part]);
            return result.response.text();
        }

        // --- CASE 2: WORD DOCS ---
        if (ext === '.docx') {
            const docData = await mammoth.extractRawText({ path: fullPath });
            const result = await model.generateContent(`Summarize this document: ${docData.value}`);
            return result.response.text();
        }

        // --- CASE 3: PDFs ---
        if (ext === '.pdf') {
            const dataBuffer = await fs.readFile(fullPath);
            const pdfData = await pdf(dataBuffer);
            const result = await model.generateContent(`Summarize this PDF content: ${pdfData.text}`);
            return result.response.text();
        }

        return "Unsupported file type for AI summary.";
    } catch (error) {
        console.error("AI Summary Error:", error);
        return "Failed to generate summary.";
    }
}