<?php
declare(strict_types=1);
// Catálogo de todos os textos fixos do site.
// Cada item: chave => [rótulo no painel, tipo ('linha' ou 'texto'), valor padrão]
// Os valores editados no painel ficam em data/site.json, na chave "textos". Só o que o dono
// mudou é guardado lá; o resto usa o padrão abaixo, então melhorias aqui chegam ao site sozinhas.

function catalogo_textos(): array
{
    return [
        'Cabeçalho e rodapé' => [
            'pular_conteudo'   => ['Link "Pular para o conteúdo" (aparece ao usar o teclado)', 'linha', 'Pular para o conteúdo'],
            'menu_inicio'      => ['Menu: Início', 'linha', 'Início'],
            'menu_sobre'       => ['Menu: Sobre', 'linha', 'Sobre'],
            'menu_servicos'    => ['Menu: Serviços', 'linha', 'Serviços'],
            'menu_processo'    => ['Menu: Como funciona', 'linha', 'Como funciona'],
            'menu_projetos'    => ['Menu: Projetos (aparece quando houver projetos)', 'linha', 'Projetos'],
            'menu_faq'         => ['Menu: Perguntas frequentes', 'linha', 'Perguntas'],
            'menu_contato'     => ['Menu: Contato', 'linha', 'Contato'],
            'menu_politica'    => ['Rodapé: link da Política de privacidade', 'linha', 'Política de privacidade'],
            'menu_abrir'       => ['Botão do menu no celular (texto para leitores de tela)', 'linha', 'Abrir menu'],
            'btn_orcamento'    => ['Botão do topo (pedir orçamento)', 'linha', 'Pedir orçamento'],
            'btn_flutuante'    => ['Botão flutuante (abre o WhatsApp)', 'linha', 'Pedir orçamento no WhatsApp'],
            'btn_flutuante_curto' => ['Botão flutuante: texto curto visível', 'linha', 'WhatsApp'],
            'btn_topo'         => ['Botão "voltar ao topo" (texto para leitores de tela)', 'linha', 'Voltar ao topo'],
            'atendimento'      => ['Frase de atendimento (topo e rodapé)', 'linha', 'Base em Sua Cidade, atendimento em todo o Brasil'],
            'rodape_descricao' => ['Rodapé: descrição curta', 'texto', 'Crio sites para empresas e profissionais de todo o Brasil, com base em Sua Cidade. Cada site é pensado primeiro para o celular e deixa o WhatsApp a um toque do cliente.'],
            'rodape_cta_titulo' => ['Rodapé: título da faixa de chamada', 'linha', 'Quer um site para o seu negócio?'],
            'rodape_cta_texto'  => ['Rodapé: texto da faixa de chamada', 'linha', 'Me chame no WhatsApp e conte o que você faz. Eu respondo com os próximos passos.'],
            'rodape_cta_botao'  => ['Rodapé: botão da faixa de chamada', 'linha', 'Pedir orçamento no WhatsApp'],
            'rodape_col_navegacao' => ['Rodapé: título da coluna Navegação', 'linha', 'Navegação'],
            'rodape_col_contato'   => ['Rodapé: título da coluna Contato', 'linha', 'Contato'],
        ],
        'Página inicial' => [
            'selo'             => ['Selo acima do nome', 'linha', 'Disponível para novos projetos'],
            'hero_titulo'      => ['Título principal (o que o cliente ganha)', 'linha', 'Site pronto, no seu nome, sem dor de cabeça.'],
            'hero_texto'       => ['Texto abaixo do título', 'texto', 'Eu faço o site e cuido do domínio e da hospedagem. Quem chega vê o que você faz, onde atende e o botão do WhatsApp, tudo na primeira tela.'],
            'hero_nota'        => ['Frase pequena abaixo dos botões (separe com ·; no celular aparecem só os 3 primeiros itens)', 'linha', 'Orçamento sem compromisso · Domínio registrado em seu nome · Contrato por escrito, com 3 rodadas de revisão do design · Garantia de 90 dias contra defeitos de execução, após o aceite final'],
            'btn_hero'         => ['Botão principal do topo', 'linha', 'Pedir orçamento no WhatsApp'],
            'btn_ver_projetos' => ['Segundo botão do topo, quando há projetos (leva aos projetos)', 'linha', 'Ver projetos'],
            'btn_como_funciona' => ['Segundo botão do topo, enquanto não há projetos (leva a "Como funciona")', 'linha', 'Como funciona'],
            'terminal_titulo'  => ['Seção do terminal: título', 'linha', 'Conheça meu jeito de trabalhar'],
            'terminal_texto'   => ['Seção do terminal: texto', 'texto', 'Pergunte no terminal sobre prazo, contrato, mensalidade e o que está incluído. A resposta vem na hora e sem enrolação, do jeito que eu trabalho. Se preferir conversar, o WhatsApp está a um toque.'],
            'terminal_boasvindas' => ['Seção do terminal: primeira linha dentro do terminal', 'texto', 'Oi! Escreva a sua pergunta abaixo ou fale com nosso assistente do site. Digite ajuda para ver todos os comandos.'],
            'terminal_perguntas'  => ['Seção do terminal: perguntas prontas (cada uma como "pergunta|comando", separadas por ·; o comando é um destes: preço, prazo, mensalidade, modelos, fidelidade, suporte, domínio, contato, serviços)', 'texto', 'Quanto custa?|preço · Quanto tempo leva?|prazo · O que a mensalidade inclui?|mensalidade · Como funciona o contrato?|modelos · Falar no WhatsApp|contato'],
            'dor_titulo'       => ['Seção de problema: frase de destaque', 'texto', 'Quem procura pelo celular chama quem explica melhor.'],
            'dor_texto'        => ['Seção de problema: texto', 'texto', 'Se o seu site deixa dúvida sobre o que você faz ou onde atende, a pessoa fecha a página e chama o próximo.'],
            'dor_ruim_titulo'  => ['Seção de problema: título da coluna "deixa dúvida"', 'linha', 'Site que deixa dúvida'],
            'dor_ruim_itens'   => ['Seção de problema: itens da coluna "deixa dúvida" (separe com ·)', 'texto', 'Não diz o que a empresa faz · Não diz onde atende · Sem fotos do trabalho · Contato escondido'],
            'dor_bom_titulo'   => ['Seção de problema: título da coluna "explica"', 'linha', 'Site que explica'],
            'dor_bom_itens'    => ['Seção de problema: itens da coluna "explica" (separe com ·)', 'texto', 'Mostra o que você faz na primeira tela · Diz onde você atende · Tem fotos do trabalho · Botão do WhatsApp sempre à vista'],
            'sec_sobre'        => ['Título da seção Sobre', 'linha', 'Sobre'],
            'sobre_titulo'     => ['Seção Sobre: título da seção na página inicial', 'linha', 'Quem faz o seu site'],
            'sobre_carta'      => ['Seção Sobre: três promessas em primeira pessoa (separe com ·; cada uma é "Destaque: frase")', 'texto', 'Antes: entendo o seu negócio e como os seus clientes preferem falar com você. · Na entrega: reviso pessoalmente cada página antes de ir ao ar. · No contrato: prazo e valores ficam combinados por escrito.'],
            'sec_para_quem'    => ['Título da seção Para quem é', 'linha', 'Qual é o seu tipo de negócio?'],
            'seg_intro'        => ['Seção Para quem é: frase abaixo do título', 'texto', 'Toque no seu ramo e veja o que o seu site teria.'],
            'seg_grupo'        => ['Seção Para quem é: nome do grupo de botões (leitor de tela)', 'linha', 'Ramos de atuação'],
            'seg_botao'        => ['Seção Para quem é: botão do ramo escolhido ({ramo} vira o nome do ramo)', 'linha', 'Pedir orçamento para {ramo}'],
            'seg_msg'          => ['Seção Para quem é: mensagem do WhatsApp ({ramo} vira o nome do ramo)', 'texto', 'Olá! Meu negócio é do ramo de {ramo} e quero saber como fica o meu site.'],
            'sec_servicos'     => ['Título da seção Serviços', 'linha', 'Serviços de criação de sites'],
            'btn_saiba_mais'   => ['Link "Saiba mais" dos serviços', 'linha', 'Saiba mais'],
            'btn_orcamento_servico' => ['Botão de orçamento dentro de cada serviço', 'linha', 'Pedir orçamento deste serviço'],
            'btn_detalhes_servico'  => ['Link para a página completa do serviço', 'linha', 'Ver detalhes completos'],
            'sec_contratar'    => ['Título da seção Como contratar', 'linha', 'Dois jeitos de contratar'],
            'contratar_intro'  => ['Como contratar: introdução', 'texto', 'Você escolhe o modelo na proposta. Os valores vêm no orçamento, antes de você assinar.'],
            'mod1_nome'        => ['Modelo 1: nome', 'linha', 'Criação + mensalidade'],
            'mod1_texto'       => ['Modelo 1: texto', 'texto', 'Você paga a criação do site e uma mensalidade. Depois de quitar a criação, o código e o design passam a ser seus. Inclui até 3 rodadas de revisão.'],
            'mod2_nome'        => ['Modelo 2: nome', 'linha', 'Assinatura'],
            'mod2_texto'       => ['Modelo 2: texto', 'texto', 'Sem taxa de criação: você paga só a mensalidade e usa o site por licença. Inclui até 2 rodadas de revisão. Depois dos 12 meses, quem quiser ficar com os arquivos paga uma Taxa de Liberação única, combinada em contrato. Sem a taxa, a licença é encerrada e o site sai do ar, com aviso de 30 dias.'],
            'inclui_titulo'    => ['O que a mensalidade inclui: título', 'linha', 'O que a mensalidade inclui'],
            'inclui_lista'     => ['O que a mensalidade inclui: lista (um item por linha)', 'texto', "Hospedagem em servidores no Brasil\nRenovação anual do domínio, que fica no seu nome\nBackup semanal, com as cópias das últimas 4 semanas\nSuporte de segunda a sexta, das 8h às 18h, com resposta em até 48 horas úteis\nAtualizações de conteúdo: até 2 horas ou 3 pedidos simples por mês (o que não usar não acumula)"],
            'regras_texto'     => ['Fidelidade, reajuste e cancelamento', 'texto', 'A mensalidade tem fidelidade mínima de 12 meses. Depois, o valor só pode ser reajustado uma vez por ano, limitado ao IPCA, com 30 dias de aviso, e você cancela com 30 dias de aviso, sem multa. Cancelando antes dos 12 meses, a multa é de 20% das mensalidades que faltam.'],
            'sec_processo'     => ['Título da seção Como funciona', 'linha', 'Como funciona'],
            'sec_projetos'     => ['Título da seção Projetos', 'linha', 'Projetos de demonstração'],
            'btn_ver_site'     => ['Link "Ver site" dos projetos', 'linha', 'Ver site'],
            'btn_ver_todos'    => ['Link "Ver todos os projetos"', 'linha', 'Ver todos os projetos'],
            'proj_vazio_tag'     => ['Projetos em breve: etiqueta', 'linha', 'em produção'],
            'proj_vazio_titulo'  => ['Projetos em breve: título', 'linha', 'Estou montando as primeiras demonstrações'],
            'proj_vazio_texto'   => ['Projetos em breve: texto', 'texto', 'São sites de exemplo para restaurantes, clínicas, salões, oficinas e escritórios. Quando ficarem prontos, aparecem aqui com o print da tela inicial e o link para o site completo.'],
            'proj_vazio_botao'   => ['Projetos em breve: botão', 'linha', 'Pedir orçamento no WhatsApp'],
            'proj_vazio_selo'    => ['Projetos em breve: selo dos cartões', 'linha', 'Em breve'],
            'cta_meio_titulo'  => ['Chamada no meio da página: título', 'linha', 'Quer ver como ficaria o seu site?'],
            'cta_meio_texto'   => ['Chamada no meio da página: texto', 'linha', 'Conte um pouco do que você faz. Eu respondo com uma ideia de como o site poderia ficar e do que faria sentido incluir.'],
            'sec_depoimentos'  => ['Título da seção Depoimentos', 'linha', 'Depoimentos'],
            'sec_faq'          => ['Título da seção Perguntas frequentes', 'linha', 'Perguntas frequentes sobre criação de sites'],
            'sec_contato'      => ['Título da seção Contato', 'linha', 'Contato'],
            'contato_titulo'   => ['Título da seção de contato', 'linha', 'Vamos conversar sobre o seu site?'],
            'contato_texto'    => ['Texto da seção de contato', 'texto', 'Conte o que você faz e para quem. Respondo em até 24 horas úteis com os próximos passos.'],
            'contato_quem_titulo' => ['Contato: título do cartão "quem responde"', 'linha', 'Quem responde sou eu, Seu Nome'],
            'contato_quem_texto'  => ['Contato: texto do cartão "quem responde"', 'texto', 'Pelo WhatsApp ou pelo e-mail que você informar.'],
            'form_nome'        => ['Campo do formulário: Nome', 'linha', 'Seu nome'],
            'form_contato'     => ['Campo do formulário: WhatsApp ou e-mail', 'linha', 'WhatsApp ou e-mail'],
            'form_contato_exemplo' => ['Exemplo dentro do campo WhatsApp ou e-mail (some ao digitar)', 'linha', 'Ex.: (11) 91234-5678 ou voce@email.com'],
            'form_ramo'        => ['Campo do formulário: Ramo do negócio', 'linha', 'Ramo do negócio (opcional)'],
            'form_ramo_exemplo' => ['Exemplo dentro do campo Ramo do negócio (some ao digitar)', 'linha', 'Ex.: padaria, clínica…'],
            'form_tem_site'    => ['Pergunta do formulário: já tem site', 'linha', 'Já tem site? (opcional)'],
            'form_sim'         => ['Resposta "sim" da pergunta acima', 'linha', 'Sim'],
            'form_nao'         => ['Resposta "não" da pergunta acima', 'linha', 'Não'],
            'form_mensagem'    => ['Campo do formulário: Mensagem', 'linha', 'Conte um pouco do seu negócio'],
            'form_privacidade' => ['Texto de privacidade abaixo do formulário (antes do link)', 'linha', 'Ao enviar, você concorda com o uso dos seus dados para responder ao pedido, conforme a'],
            'form_link_politica' => ['Texto do link para a política, abaixo do formulário', 'linha', 'Política de privacidade'],
            'btn_enviar'       => ['Botão enviar formulário', 'linha', 'Enviar mensagem'],
            'btn_enviando'     => ['Botão do formulário durante o envio', 'linha', 'Enviando…'],
            'btn_whatsapp'     => ['Botão WhatsApp (contato)', 'linha', 'Falar no WhatsApp'],
            'btn_email'        => ['Botão e-mail (contato)', 'linha', 'Enviar e-mail'],
            'aviso_ok'         => ['Aviso: mensagem enviada', 'linha', 'Mensagem recebida. Eu respondo em até 24 horas úteis, pelo WhatsApp ou pelo e-mail que você informou.'],
            'aviso_erro'       => ['Aviso: falha no envio', 'linha', 'Não consegui enviar agora. Tente de novo ou me chame no WhatsApp.'],
            'aviso_campos'     => ['Aviso: campos obrigatórios', 'linha', 'Preencha nome, contato e mensagem.'],
            'aviso_limite'     => ['Aviso: muitas mensagens', 'linha', 'Você já enviou 3 mensagens na última hora. Tente mais tarde ou me chame no WhatsApp.'],
            'aviso_expirou'    => ['Aviso: página aberta há muito tempo', 'linha', 'A página ficou aberta por muito tempo. Envie a mensagem de novo, por favor.'],
            'wa_servico'       => ['Mensagem do WhatsApp nos serviços (use {servico})', 'linha', 'Olá, Seu Nome! Tenho interesse no serviço: {servico}.'],
        ],
        'Assistente de perguntas' => [
            'chat_botao'       => ['Botão que abre o assistente', 'linha', 'Dúvidas'],
            'chat_link_faq'    => ['Botão no fim das Perguntas frequentes que abre o assistente', 'linha', 'Ficou com alguma dúvida? Pergunte ao assistente'],
            'chat_titulo'      => ['Título da janela do assistente', 'linha', 'Assistente do site'],
            'chat_saudacao'    => ['Primeira mensagem do assistente', 'texto', 'Olá! Escreva a sua dúvida abaixo. Pode mandar mais de uma dúvida na mesma mensagem.'],
            'chat_padrao'      => ['Resposta quando não há resposta pronta (use {email})', 'texto', 'Essa eu não sei responder por aqui. Mande a sua dúvida para {email} ou pelo WhatsApp, e eu respondo pessoalmente.'],
            'chat_campo'       => ['Texto de exemplo no campo de pergunta', 'linha', 'Digite sua dúvida…'],
            'chat_enviar'      => ['Botão de enviar pergunta', 'linha', 'Enviar'],
            'chat_varias'      => ['Aviso quando há muitas respostas', 'linha', 'Respondi às principais dúvidas. Se faltou alguma, pergunte de novo, uma de cada vez.'],
        ],
        'Página de projetos' => [
            'proj_titulo'   => ['Título da página', 'linha', 'Projetos'],
            'proj_intro'    => ['Texto de introdução', 'texto', 'Cada demonstração mostra como um tipo de negócio pode ficar, com cores, textos e botões de contato pensados para ele. Toque em "Ver site" para abrir a versão completa e navegar como um cliente.'],
            'proj_intro_vazio' => ['Texto de introdução (enquanto não há projetos)', 'texto', 'Aqui vão ficar as demonstrações de sites para cada tipo de negócio, com cores, textos e botões de contato pensados para ele.'],
            'proj_descricao_seo' => ['Descrição para o Google (120 a 160 caracteres)', 'linha', 'Demonstração de site para escritório de advocacia (exemplo fictício). Navegue como um cliente faria.'],
            'voltar_inicio' => ['Link "Voltar para o início"', 'linha', 'Voltar para o início'],
        ],
        'Página de serviço' => [
            'serv_rotulo'     => ['Rótulo acima do título', 'linha', 'serviço'],
            'serv_para_quem'  => ['Título "Para quem é" (também na página inicial)', 'linha', 'Para quem é'],
            'serv_incluso'    => ['Título "O que está incluso" (também na página inicial)', 'linha', 'O que está incluso'],
            'serv_resultado'  => ['Rótulo "Resultado" (também na página inicial)', 'linha', 'Resultado'],
            'serv_orcamento'  => ['Botão de orçamento', 'linha', 'Pedir orçamento no WhatsApp'],
            'serv_contato'    => ['Botão de formulário', 'linha', 'Enviar mensagem'],
            'serv_outros'     => ['Título: outros serviços', 'linha', 'Outros serviços'],
            'serv_voltar'     => ['Link "Voltar para os serviços"', 'linha', 'Voltar para os serviços'],
            'serv_descricao_extra' => ['Complemento da descrição para o Google, quando a descrição do serviço for curta', 'linha', 'Atendimento em todo o Brasil, com orçamento sem compromisso pelo WhatsApp.'],
            'serv_404_titulo' => ['Título quando o serviço não existe', 'linha', 'Serviço não encontrado'],
            'serv_404_texto'  => ['Texto quando o serviço não existe', 'linha', 'Este endereço não existe mais. Veja os serviços disponíveis na página inicial.'],
        ],
        'Política de privacidade' => [
            'pol_titulo'        => ['Título da página', 'linha', 'Política de privacidade'],
            'pol_descricao_seo' => ['Descrição para o Google (use {nome})', 'linha', 'Como {nome} trata os dados enviados pelo site: o que é coletado, por quanto tempo fica guardado e como pedir acesso ou exclusão.'],
            'pol_rotulo'        => ['Rótulo acima do título', 'linha', 'Legal'],
            'pol_atual_rotulo'  => ['Texto da data de atualização', 'linha', 'Última atualização:'],
            'pol_data'          => ['Data da última atualização', 'linha', '27/10/2026'],
            'pol_intro'         => ['Texto de introdução', 'texto', 'Esta política explica quais dados pessoais este site coleta, por que coletamos, como guardamos e quais são os seus direitos, conforme a Lei Geral de Proteção de Dados Pessoais (Lei nº 13.709/2018, LGPD).'],
            'pol_s1_titulo'     => ['Seção 1: título', 'linha', '1. Quem é o responsável pelos dados'],
            'pol_s1_texto'      => ['Seção 1: texto (use {nome} e {email})', 'texto', '{nome} é o responsável pelo tratamento dos dados pessoais coletados neste site. Como agente de tratamento de pequeno porte, está dispensado de indicar um encarregado (Resolução CD/ANPD nº 2/2022, art. 11), mas mantém um canal direto: para qualquer assunto sobre privacidade, escreva para {email}.'],
            'pol_s2_titulo'     => ['Seção 2: título', 'linha', '2. Quais dados coletamos'],
            'pol_s2_texto'      => ['Seção 2: texto', 'texto', 'Coletamos apenas os dados que você informa no formulário de contato: nome, WhatsApp ou e-mail, o texto da mensagem e, se você quiser responder, o ramo do seu negócio e se já tem site. Para proteger o site contra spam e uso indevido, também registramos automaticamente a data e hora de cada tentativa de envio do formulário, junto com um código temporário calculado a partir do endereço IP (o IP em si não é guardado).'],
            'pol_s3_titulo'     => ['Seção 3: título', 'linha', '3. Para que usamos e qual a base legal'],
            'pol_s3_texto'      => ['Seção 3: texto', 'texto', 'Usamos os dados do formulário somente para responder ao seu pedido de orçamento ou contato. A base legal é a realização de procedimentos preliminares a um contrato, a seu pedido (art. 7º, V, da LGPD). Os registros de proteção contra abuso são tratados com base no nosso legítimo interesse em manter o site seguro (art. 7º, IX). Não usamos seus dados para enviar propaganda.'],
            'pol_s4_titulo'     => ['Seção 4: título', 'linha', '4. Medição anônima de cliques'],
            'pol_s4_texto'      => ['Seção 4: texto', 'texto', 'Contamos, de forma anônima, quantas vezes os botões de WhatsApp e de e-mail e o envio do formulário são usados. Esses totais são guardados por dia, sem nome, sem IP e sem identificador de visitante, e servem apenas para entender o que funciona no site. Para impedir contagens falsas em massa, o servidor usa por até 1 hora um código temporário calculado a partir do IP, que troca todo dia e não permite descobrir o IP de volta. Esse código é um dado pseudonimizado, tratado com base no legítimo interesse em manter o site seguro (art. 7º, IX). Não usamos ferramentas de análise de terceiros nem criamos perfis de visitantes. O assistente de perguntas do site funciona no próprio navegador: as perguntas não são enviadas a terceiros nem guardadas.'],
            'pol_s5_titulo'     => ['Seção 5: título', 'linha', '5. Cookies'],
            'pol_s5_texto'      => ['Seção 5: texto', 'texto', 'O site não usa cookies de rastreamento nem de publicidade. Quem visita o site não recebe nenhum cookie. As fontes e os demais arquivos do site são carregados do próprio servidor, sem conexão com serviços de terceiros ao abrir as páginas. Se o envio do formulário falhar, o que você escreveu fica guardado só no seu navegador, nesta aba, para não se perder, e é apagado quando a mensagem é enviada ou a aba é fechada. A escolha de pausar as animações também fica guardada só no seu navegador.'],
            'pol_s6_titulo'     => ['Seção 6: título', 'linha', '6. Com quem compartilhamos'],
            'pol_s6_texto'      => ['Seção 6: texto', 'texto', 'Não vendemos, alugamos nem repassamos seus dados para terceiros com fins comerciais. Eles são tratados apenas por fornecedores que mantêm o site e o e-mail, como a empresa de hospedagem e o provedor de e-mail, que seguem as mesmas regras de segurança. Dados podem ser entregues a autoridades quando houver exigência legal.'],
            'pol_s7_titulo'     => ['Seção 7: título', 'linha', '7. Onde os dados ficam'],
            'pol_s7_texto'      => ['Seção 7: texto', 'texto', 'O site é hospedado pela empresa de hospedagem contratada pelo responsável (ajuste este trecho ao seu caso). As mensagens do formulário também são enviadas por e-mail ao endereço de contato do responsável, que usa um provedor de e-mail (ajuste este trecho ao seu caso, informando onde ficam os servidores). Essa transferência internacional é feita para responder ao seu pedido (art. 33, IX, combinado com o art. 7º, V, da LGPD), e o provedor protege as mensagens com criptografia.'],
            'pol_s8_titulo'     => ['Seção 8: título', 'linha', '8. Como protegemos os dados'],
            'pol_s8_texto'      => ['Seção 8: texto', 'texto', 'Adotamos medidas técnicas e administrativas para proteger seus dados: conexão criptografada (HTTPS), limite de tentativas de envio do formulário e acesso restrito aos arquivos do servidor. Nenhum sistema é totalmente livre de riscos, mas trabalhamos para reduzi-los.'],
            'pol_s9_titulo'     => ['Seção 9: título', 'linha', '9. Por quanto tempo guardamos'],
            'pol_s9_texto'      => ['Seção 9: texto', 'texto', 'As mensagens recebidas ficam guardadas no servidor por até 12 meses e são apagadas depois desse prazo. A cópia enviada por e-mail e as conversas pelo WhatsApp também são apagadas em até 12 meses após o último contato, salvo se virarem um contrato, caso em que ficam guardadas enquanto ele durar e pelo prazo exigido por lei. Você também pode pedir a exclusão antes. Os totais de cliques ficam guardados por até 90 dias. Os registros de proteção são removidos automaticamente após 1 hora no formulário de contato (código temporário).'],
            'pol_s10_titulo'    => ['Seção 10: título', 'linha', '10. Seus direitos'],
            'pol_s10_texto'     => ['Seção 10: texto antes da lista', 'texto', 'Pela LGPD (art. 18), você pode pedir a qualquer momento:'],
            'pol_s10_lista'     => ['Seção 10: itens da lista (um por linha)', 'texto', "confirmação de que tratamos seus dados;\nacesso aos dados que temos sobre você;\ncorreção de dados incompletos, inexatos ou desatualizados;\nanonimização, bloqueio ou eliminação de dados desnecessários ou tratados fora da lei;\nportabilidade dos dados a outro fornecedor;\neliminação dos dados tratados com o seu consentimento;\ninformação sobre com quem os dados foram compartilhados;\nrevogação do consentimento, a qualquer momento."],
            'pol_s10_final'     => ['Seção 10: texto depois da lista', 'texto', 'Para exercer esses direitos, escreva para {email} com o assunto "Privacidade" e informe seu nome e o pedido. Respondo em até 15 dias.'],
            'pol_s11_titulo'    => ['Seção 11: título', 'linha', '11. Reclamações'],
            'pol_s11_texto'     => ['Seção 11: texto', 'texto', 'Se não ficar satisfeito com a resposta, você pode fazer uma reclamação à Autoridade Nacional de Proteção de Dados (ANPD), pelo site gov.br/anpd.'],
            'pol_s12_titulo'    => ['Seção 12: título', 'linha', '12. Crianças e adolescentes'],
            'pol_s12_texto'     => ['Seção 12: texto', 'texto', 'Este site não é direcionado a menores de 18 anos e não coletamos dados de crianças de forma intencional. Se você é responsável por um menor que enviou dados pelo formulário, escreva para {email} para que eles sejam apagados.'],
            'pol_s13_titulo'    => ['Seção 13: título', 'linha', '13. Links e alterações'],
            'pol_s13_texto'     => ['Seção 13: texto', 'texto', 'O site tem links para WhatsApp, e-mail, LinkedIn e para os sites de demonstração, que têm suas próprias políticas de privacidade. Esta política pode ser atualizada; a data no topo da página mostra a versão em vigor.'],
            'voltar_inicio_pol' => ['Link "Voltar para o início"', 'linha', 'Voltar para o início'],
        ],
    ];
}

/** Valores atuais de todos os textos (padrão, sobrescritos pelo painel). */
function textos_atuais(): array
{
    static $cache = null;
    if ($cache === null) {
        $dados = carregar_dados();
        $salvos = $dados['textos'] ?? [];
        $cache = [];
        foreach (catalogo_textos() as $grupo => $itens) {
            foreach ($itens as $chave => [$rotulo, $tipo, $padrao]) {
                $cache[$chave] = array_key_exists($chave, $salvos) ? (string) $salvos[$chave] : $padrao;
            }
        }
    }
    return $cache;
}

/** Retorna o texto de uma chave, trocando {nome} e {email} quando informados. */
function t(string $chave, array $vars = []): string
{
    $texto = textos_atuais()[$chave] ?? $chave;
    foreach ($vars as $nome => $valor) {
        $texto = str_replace('{' . $nome . '}', (string) $valor, $texto);
    }
    return $texto;
}
