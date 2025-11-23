const express = require('express');
const cors = require('cors');
require('dotenv').config();
const OpenAI = require('openai');


const openai = new OpenAI({
  apiKey: process.env.OPENAI_API_KEY,
});

const app = express();
const port = 3000;


app.use(cors());
app.use(express.json());


app.get('/', (req, res) => {
  res.send('Der Server läuft und ist bereit für KI-Anfragen! 🚀');
});


app.post('/generate-posts', async (req, res) => {
  try {

    const { content } = req.body;

    if (!content) {
      return res.status(400).send('Fehler: Es wurde kein Inhalt übermittelt.');
    }


    

    
    const prompt = `
      Analysiere den folgenden Blog-Artikel und erstelle daraus Social-Media-Posts für LinkedIn, X (Twitter) und Instagram.
      Gib deine Antwort ausschließlich als JSON-Objekt zurück, das so aussieht: {"linkedin": "Dein Text...", "x": "Dein Text...", "instagram": "Dein Text..."}
      
      Regeln:
      - LinkedIn: Seriös, professionell, 2-3 Absätze, mit relevanten Business-Hashtags.
      - X (Twitter): Kurz und prägnant, maximal 280 Zeichen, 2-3 aussagekräftige Hashtags, lockerer Ton.
      - Instagram: Ansprechend und visuell, mit Emojis, direkter Ansprache und einer Call-to-Action, 4-5 beliebte Hashtags.

      Blog-Artikel:
      """
      ${content}
      """
    `;

    
    const response = await openai.chat.completions.create({
      model: 'gpt-4o-mini',
      messages: [{ role: 'user', content: prompt }],
      response_format: { type: "json_object" }, 
    });

    
    const result = JSON.parse(response.choices[0].message.content);
    res.json(result); 
    
    


  } catch (error) {
    console.error('Fehler bei der OpenAI-Anfrage:', error);
    res.status(500).send('Ein Fehler ist aufgetreten.');
  }
});


app.listen(port, () => {
  console.log(`Server lauscht auf http://localhost:${port}`);
});