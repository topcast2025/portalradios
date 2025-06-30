<?php
/**
 * Página de cadastro de rádio (exemplo de uso da API)
 */

// Headers de segurança
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Processar formulário se enviado
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validar dados
        $required_fields = ['name', 'email', 'radio_name', 'stream_url', 'brief_description', 'country', 'language'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Campo '$field' é obrigatório");
            }
        }
        
        // Preparar dados
        $data = [
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email']),
            'radio_name' => trim($_POST['radio_name']),
            'stream_url' => trim($_POST['stream_url']),
            'brief_description' => trim($_POST['brief_description']),
            'detailed_description' => trim($_POST['detailed_description'] ?? ''),
            'genres' => $_POST['genres'] ?? [],
            'country' => trim($_POST['country']),
            'language' => trim($_POST['language']),
            'website' => trim($_POST['website'] ?? ''),
            'whatsapp' => trim($_POST['whatsapp'] ?? ''),
            'facebook' => trim($_POST['facebook'] ?? ''),
            'instagram' => trim($_POST['instagram'] ?? ''),
            'twitter' => trim($_POST['twitter'] ?? '')
        ];
        
        // Chamar API
        $api_url = 'https://wave.soradios.online/api/radios';
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($data),
                'timeout' => 30
            ]
        ]);
        
        $response = @file_get_contents($api_url, false, $context);
        if ($response) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                $message = 'Rádio cadastrada com sucesso! Aguarde aprovação.';
                $message_type = 'success';
                // Limpar formulário
                $_POST = [];
            } else {
                $message = $result['message'] ?? 'Erro desconhecido';
                $message_type = 'error';
            }
        } else {
            $message = 'Erro ao conectar com a API';
            $message_type = 'error';
        }
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $message_type = 'error';
    }
}

// Gêneros disponíveis
$genres = [
    'MPB', 'Rock', 'Pop', 'Sertanejo', 'Forró', 'Funk', 'Samba', 'Pagode',
    'Jazz', 'Blues', 'Classical', 'Electronic', 'Hip Hop', 'Reggae',
    'Country', 'Folk', 'Gospel', 'News', 'Talk', 'Sports'
];

// Países
$countries = [
    'Brasil', 'Argentina', 'Chile', 'Colombia', 'Peru', 'Uruguay',
    'Portugal', 'Spain', 'France', 'Italy', 'Germany', 'USA'
];

// Idiomas
$languages = [
    'portuguese', 'spanish', 'english', 'french', 'italian', 'german'
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Rádio - RadioWave</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            min-height: 100vh;
        }
        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .card {
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(71, 85, 105, 0.5);
            border-radius: 24px;
        }
    </style>
</head>
<body class="text-white">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-12">
                <a href="index.php" class="inline-flex items-center text-purple-400 hover:text-purple-300 mb-6">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar ao Início
                </a>
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    Cadastrar <span class="text-gradient">Rádio</span>
                </h1>
                <p class="text-xl text-gray-400">
                    Adicione sua rádio ao nosso diretório
                </p>
            </div>

            <!-- Message -->
            <?php if ($message): ?>
            <div class="mb-8 p-4 rounded-xl <?php echo $message_type === 'success' ? 'bg-green-500/20 border border-green-500/30 text-green-400' : 'bg-red-500/20 border border-red-500/30 text-red-400'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <!-- Form -->
            <div class="card p-8">
                <form method="POST" class="space-y-8">
                    <!-- Informações Pessoais -->
                    <div>
                        <h3 class="text-2xl font-bold text-white mb-6">Informações Pessoais</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-3">Nome *</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required
                                       class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400"
                                       placeholder="Seu nome completo">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-3">Email *</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required
                                       class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400"
                                       placeholder="seu@email.com">
                            </div>
                        </div>
                    </div>

                    <!-- Informações da Rádio -->
                    <div>
                        <h3 class="text-2xl font-bold text-white mb-6">Informações da Rádio</h3>
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-3">Nome da Rádio *</label>
                                <input type="text" name="radio_name" value="<?php echo htmlspecialchars($_POST['radio_name'] ?? ''); ?>" required
                                       class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400"
                                       placeholder="Nome da sua rádio">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-3">URL do Stream *</label>
                                <input type="url" name="stream_url" value="<?php echo htmlspecialchars($_POST['stream_url'] ?? ''); ?>" required
                                       class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400"
                                       placeholder="https://seustream.com:8888/stream">
                                <p class="mt-2 text-sm text-gray-400">Utilize HTTPS para melhor compatibilidade</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-3">Descrição Breve *</label>
                                <textarea name="brief_description" rows="3" required maxlength="500"
                                          class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400 resize-none"
                                          placeholder="Descrição curta da sua rádio (máximo 500 caracteres)"><?php echo htmlspecialchars($_POST['brief_description'] ?? ''); ?></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-3">Descrição Detalhada</label>
                                <textarea name="detailed_description" rows="6"
                                          class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400 resize-none"
                                          placeholder="Informações detalhadas, WhatsApp, redes sociais, etc."><?php echo htmlspecialchars($_POST['detailed_description'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Gêneros -->
                    <div>
                        <h3 class="text-2xl font-bold text-white mb-6">Gêneros *</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                            <?php foreach ($genres as $genre): ?>
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="genres[]" value="<?php echo $genre; ?>" 
                                       <?php echo in_array($genre, $_POST['genres'] ?? []) ? 'checked' : ''; ?>
                                       class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                <span class="text-sm text-gray-300"><?php echo $genre; ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Localização -->
                    <div>
                        <h3 class="text-2xl font-bold text-white mb-6">Localização</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-3">País *</label>
                                <select name="country" required
                                        class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white">
                                    <option value="">Selecione o país</option>
                                    <?php foreach ($countries as $country): ?>
                                    <option value="<?php echo $country; ?>" <?php echo ($_POST['country'] ?? '') === $country ? 'selected' : ''; ?>>
                                        <?php echo $country; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-3">Idioma *</label>
                                <select name="language" required
                                        class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white">
                                    <option value="">Selecione o idioma</option>
                                    <?php foreach ($languages as $language): ?>
                                    <option value="<?php echo $language; ?>" <?php echo ($_POST['language'] ?? '') === $language ? 'selected' : ''; ?>>
                                        <?php echo ucfirst($language); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Redes Sociais -->
                    <div>
                        <h3 class="text-2xl font-bold text-white mb-6">Redes Sociais (Opcional)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-3">Website</label>
                                <input type="url" name="website" value="<?php echo htmlspecialchars($_POST['website'] ?? ''); ?>"
                                       class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400"
                                       placeholder="https://seusite.com">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-3">WhatsApp</label>
                                <input type="tel" name="whatsapp" value="<?php echo htmlspecialchars($_POST['whatsapp'] ?? ''); ?>"
                                       class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400"
                                       placeholder="+55 11 99999-9999">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-3">Facebook</label>
                                <input type="url" name="facebook" value="<?php echo htmlspecialchars($_POST['facebook'] ?? ''); ?>"
                                       class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400"
                                       placeholder="https://facebook.com/suaradio">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-3">Instagram</label>
                                <input type="url" name="instagram" value="<?php echo htmlspecialchars($_POST['instagram'] ?? ''); ?>"
                                       class="w-full px-4 py-3 bg-slate-700/50 border border-slate-600/50 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 text-white placeholder-gray-400"
                                       placeholder="https://instagram.com/suaradio">
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex justify-end space-x-4 pt-8 border-t border-slate-700/50">
                        <a href="index.php" class="px-8 py-3 bg-slate-700/50 hover:bg-slate-600/50 text-white rounded-xl transition-colors">
                            Cancelar
                        </a>
                        <button type="submit" class="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl transition-all duration-300 font-semibold">
                            Cadastrar Rádio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
```