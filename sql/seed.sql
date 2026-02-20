-- ============================================
-- Saxho.net — Donnees de demonstration
-- ============================================

SET NAMES utf8mb4;

-- -------------------------------------------
-- Administrateur
-- Mot de passe par defaut : Saxho2025! (a changer)
-- Hash genere avec password_hash('Saxho2025!', PASSWORD_BCRYPT)
-- -------------------------------------------
INSERT INTO `users` (`email`, `password_hash`, `first_name`, `last_name`, `company`, `job_title`, `role`, `email_verified`, `is_active`)
VALUES (
    'xavier@saxho.net',
    '$2b$10$PmvFPMngjjMTH2SXiSSkhORSoYDn6hCplcaKkbq4QAQlyH8BBrxWi',
    'Xavier',
    'Hovasse',
    'Saxho',
    'Dirigeant',
    'admin',
    1,
    1
);

-- -------------------------------------------
-- Projets portfolio (5 projets fictifs)
-- -------------------------------------------

-- Projet 1 : Pulse
INSERT INTO `projects` (`name_fr`, `name_en`, `slug`, `pitch_fr`, `pitch_en`, `domain`, `problem_fr`, `problem_en`, `solution_fr`, `solution_en`, `phase`, `investment_sought`, `skills_sought_fr`, `skills_sought_en`, `launch_date`, `status`, `is_visible`, `display_order`)
VALUES (
    'Pulse',
    'Pulse',
    'pulse',
    'Une solution connectee qui transforme le suivi de la recuperation sportive en un outil predictif personnalise.',
    'A connected solution that transforms sports recovery tracking into a personalized predictive tool.',
    'health',
    'Les sportifs amateurs n''ont aucun moyen fiable de savoir quand leur corps est pret pour un nouvel effort intense.',
    'Amateur athletes have no reliable way to know when their body is ready for another intense effort.',
    'Algorithme d''analyse combinant donnees biometriques et habitudes pour predire les fenetres de performance optimale.',
    'Analysis algorithm combining biometric data and habits to predict optimal performance windows.',
    'prototype',
    '80 000 - 120 000 EUR',
    'Data science, physiologie du sport, developpement mobile',
    'Data science, sports physiology, mobile development',
    '2024-06-15',
    'open',
    1,
    1
);

-- Projet 2 : Greenloop
INSERT INTO `projects` (`name_fr`, `name_en`, `slug`, `pitch_fr`, `pitch_en`, `domain`, `problem_fr`, `problem_en`, `solution_fr`, `solution_en`, `phase`, `investment_sought`, `skills_sought_fr`, `skills_sought_en`, `launch_date`, `status`, `is_visible`, `display_order`)
VALUES (
    'Greenloop',
    'Greenloop',
    'greenloop',
    'Une plateforme B2B qui met en relation les dechets industriels d''une entreprise avec les besoins en matieres premieres d''une autre.',
    'A B2B platform connecting one company''s industrial waste with another''s raw material needs.',
    'circular_economy',
    'Des tonnes de sous-produits industriels sont jetes alors qu''ils pourraient etre la matiere premiere d''un autre acteur.',
    'Tons of industrial by-products are discarded when they could be raw material for another company.',
    'Marketplace intelligente avec matching automatique base sur la composition chimique et la geolocalisation.',
    'Smart marketplace with automatic matching based on chemical composition and geolocation.',
    'study',
    '150 000 - 200 000 EUR',
    'Chimie industrielle, logistique, developpement web full-stack',
    'Industrial chemistry, logistics, full-stack web development',
    '2024-09-01',
    'open',
    1,
    2
);

-- Projet 3 : Topo
INSERT INTO `projects` (`name_fr`, `name_en`, `slug`, `pitch_fr`, `pitch_en`, `domain`, `problem_fr`, `problem_en`, `solution_fr`, `solution_en`, `phase`, `investment_sought`, `skills_sought_fr`, `skills_sought_en`, `launch_date`, `status`, `is_visible`, `display_order`)
VALUES (
    'Topo',
    'Topo',
    'topo',
    'Un outil qui genere automatiquement des parcours de montee en competences a partir de l''analyse des pratiques reelles d''un collaborateur.',
    'A tool that automatically generates upskilling paths based on the analysis of an employee''s actual work practices.',
    'edtech',
    'Les plans de formation sont souvent deconnectes des besoins reels et arrivent trop tard.',
    'Training plans are often disconnected from actual needs and come too late.',
    'Observation non intrusive des pratiques de travail et generation de micro-formations ciblees en temps reel.',
    'Non-intrusive observation of work practices and real-time generation of targeted micro-training.',
    'ideation',
    '50 000 - 80 000 EUR',
    'Machine learning, UX design, sciences de l''education',
    'Machine learning, UX design, education sciences',
    '2025-01-10',
    'open',
    1,
    3
);

-- Projet 4 : Nebula
INSERT INTO `projects` (`name_fr`, `name_en`, `slug`, `pitch_fr`, `pitch_en`, `domain`, `problem_fr`, `problem_en`, `solution_fr`, `solution_en`, `phase`, `investment_sought`, `skills_sought_fr`, `skills_sought_en`, `launch_date`, `status`, `is_visible`, `display_order`)
VALUES (
    'Nebula',
    'Nebula',
    'nebula',
    'Un systeme de pilotage energetique pour coproprietes qui optimise collectivement la consommation sans contraindre individuellement.',
    'An energy management system for condominiums that collectively optimizes consumption without individual constraints.',
    'energy',
    'Dans une copropriete, l''optimisation energetique bute sur la multiplicite des decideurs et l''absence de vision globale.',
    'In condominiums, energy optimization is hindered by the multiplicity of decision-makers and the lack of a global vision.',
    'Capteurs partages et algorithme d''optimisation collective avec benefice redistribue a chaque coproprietaire.',
    'Shared sensors and collective optimization algorithm with benefits redistributed to each co-owner.',
    'development',
    '200 000 - 300 000 EUR',
    'IoT, energie batiment, droit de la copropriete, developpement embarque',
    'IoT, building energy, condominium law, embedded development',
    '2024-03-20',
    'open',
    1,
    4
);

-- Projet 5 : Passerelle
INSERT INTO `projects` (`name_fr`, `name_en`, `slug`, `pitch_fr`, `pitch_en`, `domain`, `problem_fr`, `problem_en`, `solution_fr`, `solution_en`, `phase`, `investment_sought`, `skills_sought_fr`, `skills_sought_en`, `launch_date`, `status`, `is_visible`, `display_order`)
VALUES (
    'Passerelle',
    'Gateway',
    'passerelle',
    'Une application de covoiturage hyper-local dediee aux trajets domicile-travail en zones periurbaines mal desservies.',
    'A hyper-local carpooling app dedicated to home-work commutes in underserved suburban areas.',
    'mobility',
    'Les zones periurbaines sans transport en commun generent une dependance totale a la voiture individuelle.',
    'Suburban areas without public transport create total dependence on individual cars.',
    'Matching de voisinage automatique base sur les horaires reels de travail, avec micro-compensation financiere.',
    'Automatic neighborhood matching based on actual work schedules, with micro financial compensation.',
    'pre_launch',
    '100 000 - 150 000 EUR',
    'Developpement mobile, marketing territorial, partenariats collectivites',
    'Mobile development, territorial marketing, local government partnerships',
    '2023-11-05',
    'open',
    1,
    5
);

-- -------------------------------------------
-- Categories blog
-- -------------------------------------------
INSERT INTO `blog_categories` (`name_fr`, `name_en`, `slug`) VALUES
('Innovation', 'Innovation', 'innovation'),
('Methodes', 'Methods', 'methodes'),
('Retours d''experience', 'Case Studies', 'retours-experience'),
('Ecosysteme', 'Ecosystem', 'ecosysteme');

-- -------------------------------------------
-- Articles blog de demonstration
-- -------------------------------------------
INSERT INTO `blog_posts` (`title_fr`, `title_en`, `slug`, `content_fr`, `content_en`, `excerpt_fr`, `excerpt_en`, `category_id`, `author_id`, `status`, `published_at`, `reading_time`)
VALUES (
    'Pourquoi l''innovation est-elle si difficile a mettre en oeuvre ?',
    'Why is innovation so hard to implement?',
    'pourquoi-innovation-difficile',
    '<p>Chaque dirigeant sait que l''innovation est essentielle. Pourtant, la plupart des entreprises peinent a la concretiser. Ce paradoxe n''est pas le fruit du hasard : il est ancre dans la structure meme des organisations.</p><p>L''innovation exige de l''experimentation, de l''incertitude et du temps. Or, les entreprises sont construites pour l''execution, la previsibilite et l''efficacite. Ces deux logiques s''affrontent au quotidien.</p><p>La cle n''est pas de choisir l''une au detriment de l''autre, mais de creer les conditions pour qu''elles coexistent. C''est exactement ce que permettent les processus d''innovation bien integres.</p>',
    '<p>Every leader knows innovation is essential. Yet most companies struggle to make it happen. This paradox is no accident: it is rooted in the very structure of organizations.</p><p>Innovation requires experimentation, uncertainty, and time. But companies are built for execution, predictability, and efficiency. These two logics clash daily.</p><p>The key is not choosing one over the other, but creating conditions for them to coexist. This is exactly what well-integrated innovation processes enable.</p>',
    'Chaque dirigeant sait que l''innovation est essentielle. Pourtant, la plupart des entreprises peinent a la concretiser.',
    'Every leader knows innovation is essential. Yet most companies struggle to make it happen.',
    1,
    1,
    'published',
    '2025-01-15 10:00:00',
    2
),
(
    'L''ideation : comment faire emerger les bonnes idees ?',
    'Ideation: how to surface the right ideas?',
    'ideation-comment-faire-emerger-idees',
    '<p>L''ideation n''est pas un brainstorming chaotique. C''est un processus structure qui mobilise l''intelligence collective pour generer des solutions pertinentes a des problemes reels.</p><p>La methodologie que nous appliquons repose sur trois piliers : la divergence (generer un maximum d''idees sans jugement), la convergence (selectionner les plus prometteuses) et le prototypage rapide (tester avant d''investir).</p><p>Les sessions d''ideation bien animees produisent regulierement des resultats surprenants, car elles liberent la creativite des contraintes du quotidien.</p>',
    '<p>Ideation is not chaotic brainstorming. It is a structured process that mobilizes collective intelligence to generate relevant solutions to real problems.</p><p>Our methodology rests on three pillars: divergence (generating maximum ideas without judgment), convergence (selecting the most promising), and rapid prototyping (testing before investing).</p><p>Well-facilitated ideation sessions regularly produce surprising results because they free creativity from everyday constraints.</p>',
    'L''ideation n''est pas un brainstorming chaotique. C''est un processus structure qui produit des resultats concrets.',
    'Ideation is not chaotic brainstorming. It is a structured process that produces concrete results.',
    2,
    1,
    'published',
    '2025-02-01 09:00:00',
    2
),
(
    'Quand l''innovation devient un levier de cohesion en entreprise',
    NULL,
    'innovation-levier-cohesion-entreprise',
    '<p>Quiet quitting, perte de sens, desengagement silencieux… Ces mots reviennent en boucle dans les medias, les rapports RH, les conversations de managers. Et derriere ces expressions, une realite concrete et preoccupante : des collaborateurs qui font le minimum, qui s''eloignent progressivement de leur entreprise, parfois sans que personne ne s''en apercoive vraiment — jusqu''a ce qu''il soit trop tard.</p><p>Les impacts sont majeurs. Pour les individus d''abord, qui perdent le gout de leur metier. Pour les equipes ensuite, dont la dynamique collective se fragilise. Et pour l''organisation enfin, qui voit sa performance s''eroder en silence.</p><p>Alors, comment reengager les gens ? Comment redonner du souffle, du sens, de l''elan ?</p><p>Il n''existe pas de solution miracle. Aucune demarche ne regle a elle seule les dysfonctionnements profonds d''une organisation. Mais certains leviers, bien actives, peuvent changer la donne. L''innovation collective en fait partie.</p><h2>L''innovation, bien plus qu''un projet — une invitation</h2><p>Quand on parle d''innovation en entreprise, on pense souvent a des equipes dediees, des budgets R&amp;D, des feuilles de route produit. Bref, un territoire reserve a quelques-uns.</p><p>Pourtant, une demarche d''innovation bien concue peut — et devrait — etre tout le contraire : une invitation ouverte, adressee a l''ensemble des collaborateurs, quelle que soit leur fonction, leur anciennete, leur niveau hierarchique.</p><p>Organiser une demarche collective d''innovation, c''est envoyer un message fort : <strong>vos idees comptent</strong>. Votre regard sur ce qui ne fonctionne pas, sur ce qui pourrait etre ameliore, sur ce que nos clients vivent — tout ca a de la valeur ici.</p><h2>Ce que ca change, concretement</h2><p>Quand un collaborateur voit son idee prise au serieux, exploree, developpee — parfois jusqu''a devenir realite — quelque chose se reactive. Un sentiment d''utilite. Une fierte d''appartenance. Une envie de contribuer a nouveau.</p><p>Ce n''est pas de la magie. C''est de la reconnaissance a l''etat pur.</p><p>Participer a un processus d''innovation, c''est aussi comprendre autrement son entreprise : ses enjeux, ses contraintes, ses ambitions. C''est sortir du tunnel de sa propre mission pour voir le tableau d''ensemble. Et ce changement de perspective, souvent, redonne du sens la ou il s''etait perdu.</p><p>Pour les equipes, l''effet est tout aussi tangible. Travailler ensemble sur un defi commun — en dehors des rapports hierarchiques habituels, sur un terrain ou chacun peut apporter quelque chose — cree de la proximite, de la confiance, de la solidarite. Des ingredients rares, et precieux.</p><h2>Le role cle de la methode</h2><p>Bien sur, tout ne repose pas sur la bonne intention. Une demarche d''innovation mal structuree peut produire l''effet inverse : des idees qui tombent dans un puits sans fond, des promesses non tenues, une frustration decuplee.</p><p>C''est pourquoi la methode compte autant que l''intention. Il s''agit de :</p><ul><li><strong>Valoriser l''emergence des idees</strong> — creer des espaces et des rituels ou chacun se sent legitime pour proposer, questionner, imaginer, sans crainte du jugement.</li><li><strong>Associer les collaborateurs au developpement</strong> — ne pas s''arreter a la boite a idees. Impliquer ceux qui ont propose dans la phase d''exploration, de test, d''iteration. C''est la que l''engagement se construit vraiment.</li><li><strong>Rendre visible ce qui se passe</strong> — communiquer sur les idees retenues, expliquer pourquoi certaines ne sont pas allees plus loin, celebrer les avancees. La transparence est le carburant de la confiance.</li></ul><h2>Innovation et cohesion : deux faces d''une meme piece</h2><p>Au fond, ce qui relie l''innovation et la cohesion, c''est une question de place. Quelle place occupe chaque collaborateur dans son entreprise ? Est-ce qu''il se sent acteur, ou simple executant ? Est-ce que sa voix a du poids, ou disparait-elle dans le bruit ambiant ?</p><p>Une demarche d''innovation collective bien menee repond a ces questions — pas par des discours, mais par des actes. Elle montre, de facon concrete et vecue, que chaque personne a une contribution unique a apporter. Et ca, c''est l''une des formes les plus puissantes de mobilisation qui soit.</p><p>Parce qu''on ne s''engage pas pour une entreprise. On s''engage pour un projet dans lequel on se reconnait — et pour une communaute qui nous reconnait.</p><p><strong>Vous souhaitez structurer une demarche d''innovation qui mobilise vos equipes ? C''est exactement ce que nous accompagnons chez Saxho.</strong></p>',
    NULL,
    'Quiet quitting, perte de sens, desengagement silencieux… Et si l''innovation collective etait la cle pour reengager vos equipes ?',
    NULL,
    1,
    1,
    'published',
    '2026-02-20 10:00:00',
    4
);
