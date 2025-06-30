# Radion - Portal de Rádios Online

Sistema completo para cadastro e reprodução de rádios online, com integração à API Radio-Browser e sistema próprio de cadastro de rádios customizadas.

## 🚀 Funcionalidades

### Frontend (React + TypeScript)
- ✅ Interface moderna com design dark theme
- ✅ Player de áudio integrado com controles avançados
- ✅ Sistema de favoritos
- ✅ Busca e filtros avançados
- ✅ Integração com API Radio-Browser (nl.api.radio-browser.info)
- ✅ Cadastro de rádios customizadas
- ✅ Páginas de detalhes com estatísticas
- ✅ Sistema de reportar erros
- ✅ Edição de rádios cadastradas
- ✅ Design responsivo

### Backend (Node.js + MySQL)
- ✅ API RESTful completa
- ✅ Sistema de upload de logos
- ✅ Banco de dados MySQL com relacionamentos
- ✅ Estatísticas quinzenais automáticas
- ✅ Sistema de relatórios de erro
- ✅ Validação de dados
- ✅ Rate limiting e segurança

## 🛠️ Tecnologias

### Frontend
- React 18 + TypeScript
- Vite
- Tailwind CSS
- Framer Motion (animações)
- React Router DOM
- Axios
- React Hot Toast

### Backend
- Node.js + Express
- MySQL 8.0+
- Multer (upload de arquivos)
- Sharp (processamento de imagens)
- Express Validator
- Helmet (segurança)
- CORS

## 📦 Instalação

### 1. Configuração do Banco de Dados

Execute o script SQL para criar o banco de dados e tabelas:

```bash
mysql -u root -p < database/schema.sql
```

### 2. Backend

```bash
cd backend
npm install
cp .env.example .env
# Configure as variáveis no arquivo .env
npm run dev
```

### 3. Frontend

```bash
npm install
npm run dev
```

## 🗄️ Estrutura do Banco de Dados

### Tabelas Principais

#### `radios`
- Informações das rádios cadastradas
- Dados de contato e redes sociais
- Status de aprovação
- Contadores de cliques

#### `radio_statistics`
- Estatísticas quinzenais de acesso
- Cálculo automático via procedure
- Histórico de períodos

#### `radio_clicks`
- Log individual de cada clique
- IP, User Agent, Referrer
- Base para cálculo de estatísticas

#### `radio_error_reports`
- Relatórios de problemas
- Status de resolução
- Notas administrativas

#### `file_uploads`
- Log de uploads de arquivos
- Controle de logos enviadas

### Procedures e Triggers

- **CalculateFortnightlyStats()**: Calcula estatísticas quinzenais
- **update_radio_clicks_count**: Atualiza contador de cliques automaticamente

## 🔧 Configuração

### Variáveis de Ambiente (.env)

```env
# Database
DB_HOST=localhost
DB_USER=soradios_radion
DB_PASSWORD=Ant130915!
DB_NAME=soradios_radion

# Server
PORT=3001
NODE_ENV=development

# Upload
UPLOAD_DIR=uploads
MAX_FILE_SIZE=5242880

# Security
JWT_SECRET=your-secret-key
```

## 📡 API Endpoints

### Rádios
- `GET /api/radios` - Listar rádios (com paginação e filtros)
- `GET /api/radios/:id` - Detalhes de uma rádio
- `POST /api/radios` - Cadastrar nova rádio
- `PUT /api/radios/:id` - Atualizar rádio
- `DELETE /api/radios/:id` - Remover rádio

### Interações
- `POST /api/radios/:id/click` - Registrar clique/acesso
- `GET /api/radios/:id/statistics` - Estatísticas da rádio
- `POST /api/radios/:id/report` - Reportar problema

### Upload
- `POST /api/upload-logo` - Upload de logo
- `DELETE /api/upload/:filename` - Remover arquivo

## 🎨 Funcionalidades do Frontend

### Páginas
- **Home**: Apresentação e rádios populares
- **Browse**: Explorar rádios com filtros
- **Favorites**: Rádios favoritas do usuário
- **Register Radio**: Formulário de cadastro
- **Radio Details**: Página detalhada com estatísticas
- **About**: Informações sobre o projeto

### Componentes
- **AudioPlayer**: Player fixo na parte inferior
- **RadioCard**: Card de rádio com controles
- **SearchBar**: Busca com filtros avançados
- **Header/Footer**: Navegação e informações

## 🔒 Segurança

- Rate limiting (100 req/15min por IP)
- Validação de dados no backend
- Sanitização de uploads
- Helmet para headers de segurança
- CORS configurado
- Processamento seguro de imagens

## 📊 Estatísticas

O sistema calcula automaticamente:
- Acessos quinzenais por rádio
- Total de cliques histórico
- Períodos de maior audiência
- Relatórios de erro por rádio

## 🚀 Deploy

### Produção
1. Configure o banco MySQL
2. Execute as migrations
3. Configure variáveis de ambiente
4. Build do frontend: `npm run build`
5. Inicie o backend: `npm start`
6. Configure proxy reverso (nginx)

### Estrutura de Arquivos
```
/
├── backend/           # API Node.js
├── database/          # Scripts SQL
├── src/              # Frontend React
├── public/           # Assets estáticos
└── uploads/          # Arquivos enviados
```

## 📝 Licença

MIT License - veja o arquivo LICENSE para detalhes.

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## 📞 Suporte

Para dúvidas e suporte, entre em contato através dos canais disponíveis no sistema.