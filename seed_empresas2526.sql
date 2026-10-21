-- =============================================================================
-- Seed: Empresas curso 25/26 (Listado Empresas 25_26.xlsx)
-- Generado: 2026-10-16
--
-- 140 empresas únicas con CIF (86 sin CIF y 5 filas con CIF duplicado se omitieron)
-- 103 tutores laborales (3 empresas con varios tutores, separados en filas)
--
-- REQUISITO: ejecutar ANTES 'php artisan migrate --force'
--   (la migración 2026_10_16_120000 añade 'dni' a tutores_laborales)
--
-- Ejecutar en producción (BD ieshlanz, contenedor ieshlanz-db):
--   docker exec -i ieshlanz-db mysql -uroot -p'<PASS>' --default-character-set=utf8mb4 ieshlanz < storage/seed_empresas_25_26.sql
-- =============================================================================

START TRANSACTION;

-- 27 Megas (fila 2 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('27 Megas', '44258592Z', 'Calle San Isidro, núm 2, 18915 (Cúllar Vega)', '858125330', 'jaimebustos00@gmail.com', 'Jaime Bustos', '44258592Z', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Jaime Bustos', '44258592Z', 'jaimebustos00@gmail.com', '858125330', NOW(), NOW());

-- Academia el Futuro, S.L.U. (fila 4 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Academia el Futuro, S.L.U.', 'B18495838', 'Avda. Juan Pablo II, 64B Bajos, Granada', '958153827   655810403', 'antonio.jesus@academiafuturo.com', 'Antonio Díaz Rubio', '25304927m', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Antonio Jesús Díaz Caballero', '75149811W', NULL, NULL, NOW(), NOW());

-- Academia Puerta Real. Centro de Formación Acera del Darro (fila 5 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Academia Puerta Real. Centro de Formación Acera del Darro', 'B90104118', NULL, NULL, NULL, 'Jonathan Contreras Bustos', '75128402Y', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Activasur (fila 7 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Activasur', 'B19662931', 'C/Pianista Rosa Sabater 10', '617158788', 'coordinacion@activasur.com', 'Eduardo Arroyo Jimenez', '75564202W', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Eduardo Arroyo Jimenez', '75564202W', NULL, NULL, NOW(), NOW());

-- Aircury (fila 8 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Aircury', 'B19713437', 'C/Camino de Ronda Nº
150 Piso 5 Prta. H, 18003 GRANADA', '643 606 323', 'jobs@aircury.com', 'Jose María Díaz Angulo', NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Al Ándalus Grupo Empresarial S.L. (fila 10 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Al Ándalus Grupo Empresarial S.L.', 'B18527515', 'C/ Santa Ana n. 16', '958 22 99 78', 'j.arroyo@hammamalandalus.com', 'José María García Córdoba', '80135919D', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Jorge Arroyo Martín', '44260419R', NULL, '678788036', NOW(), NOW());

-- ANSOTEC (Buena) (fila 12 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('ANSOTEC (Buena)', 'B18822189', 'Bajo, C. Eras Bajas, 58, 18360 Huétor Tájar, Granada', '958 33 31 51', 'pilar.escobar@ansotec.com', 'Luis Alberto Guzmán Espejo', '75133293K', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Apipana.io (fila 13 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Apipana.io', 'B70688361', 'Plaza Isabel la Católica, Nº 7, 2º dcha. CP: 18002 – GRANADA', '670 684 001', 'info@apipana.io', NULL, NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Arcan Studio (fila 15 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Arcan Studio', '74658444F', NULL, '677736375', NULL, NULL, NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Juan Manuel Ferrer Martín', '74658444F', NULL, NULL, NOW(), NOW());

-- Area Binaria (fila 17 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Area Binaria', 'B18666495', NULL, '958 207 123', 'info@areabinaria.com', NULL, NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Área F5 (Desgranados desarrollo web) (fila 18 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Área F5 (Desgranados desarrollo web)', 'B19589456', 'C/ Melilla nº 1 18005- Granada', '958088098', 'info@areaf5.es', 'Jaime Martínez Martín', '74696297W', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Jaime Martínez Martín', '74696297W', NULL, '622528540', NOW(), NOW());

-- Arena Gaming (fila 20 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Arena Gaming', 'B19622976', NULL, '625864617', NULL, NULL, NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Cleo Zubeldía Belda', '76422666Z', NULL, '625 86 46 17', NOW(), NOW());

-- Arreglamos Todo (fila 22 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Arreglamos Todo', '74683288B', 'Calle Estrellas nº 20', '666243439', 'info@arreglamostodo.es', 'Damián García Ocaña', '74683288B', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Damián García Ocaña', '74683288B', 'info@arreglamostodo.es', '666243439', NOW(), NOW());

-- AssistantBot (fila 25 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('AssistantBot', 'B44639664', NULL, '651165853', 'assistantbotsl@gmail.com', NULL, '74686234J', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Atisoluciones Diseño de Sistemas Electrónicos Sl (fila 26 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Atisoluciones Diseño de Sistemas Electrónicos Sl', 'B18865253', 'C/Córdoba, parcela 2 A, polígono Asegra', '651072906', 'barco@atisoluciones.com', 'ALEJANDRO RUBIA ORTIZ', '74682572P', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- ATISOLUCIONES SEGURIDAD, SL (fila 27 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('ATISOLUCIONES SEGURIDAD, SL', 'B18896746', 'C/Córdoba, parcela 2 A, polígono Asegra', '651072906', 'barco@atisoluciones.com', 'ALEJANDRO RUBIA ORTIZ', '74682572P', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Ayuntamiento Albolote- Promoción economica y empleo (fila 30 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Ayuntamiento Albolote- Promoción economica y empleo', 'P6800408D', 'Avd. Jacobo Camarero s/n. 18220. Albolote', NULL, 'angelesperez@oal-albolote.com', 'Salustiano Ureña García', '44250176Q', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Mª ángeles Pérez Vargas', '44283164E', 'angelesperez@oal-albolote.com', '958614466', NOW(), NOW());

-- Ayuntamiento Alhendín (fila 31 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Ayuntamiento Alhendín', 'P1801500H', 'Plaza de España, Núm. 7, CP: 18620 – Alhendín - Granada', '958576136 / 627369004 Ext. 2242', 'dpo@alhendin.es', 'Jorge Rafael Sánchez Hernández', '20016360N', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Manuel Alejandro López Ginés', '52522711A', NULL, '627369004', NOW(), NOW());

-- Ayuntamiento Chauchina (fila 32 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Ayuntamiento Chauchina', 'P1808900C', NULL, NULL, NULL, NULL, NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Ayuntamiento Churriana (fila 33 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Ayuntamiento Churriana', 'P1806400F', 'Pl. de la Constitución, 14, 18194 Churriana de la Vega, Granada', NULL, NULL, NULL, NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Ayuntamiento Gabias (fila 34 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Ayuntamiento Gabias', 'P1808300F', 'Plaza de España, 1, 18110, Las Gabias. Granada', '958 58 02 61 - Ext. 8 (Mercedes RRHH)', 'formacion@lasgabias.es', 'Mª Merinda Sádaba Terribas', '24278968F', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Manuel José Villalba López', '43406935R', 'jmvillalba@lasgabias.es', '958 580 286 ext.12211', NOW(), NOW());

-- Ayuntamiento de Gójar (fila 36 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Ayuntamiento de Gójar', 'P1808600I', 'Plaza de la Constitución, 1, 18150 Gójar, Granada, Granada', '958 50 91 52', 'registro@ayuntamientogojar.com', 'José Joaquín Prieto Mora', '29771868R', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Laura Praena García', '20076634A', NULL, NULL, NOW(), NOW());

-- Ayuntamiento de Illora (fila 37 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Ayuntamiento de Illora', 'P1810400J', 'Pz Arco, s/n, 18260 Íllora, Granada', '958 43 39 26', '664468839.0', 'Antonio José Salazar Pérez', '14624206R', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'David Coca Gallego', '75145215Y', 'arearecursoshumanos@ayuntamientodeillora.com', '958 43 39 26', NOW(), NOW());

-- Ayuntamiento Maracena (fila 38 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Ayuntamiento Maracena', 'P1812800I', NULL, '958420003', 'empleo@ayuntamientomaracena.org', 'Carlos Porcel Aibar', '75134461Q', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Manuel Santaella Santaella', '24224640M', NULL, NULL, NOW(), NOW());

-- Ayuntamiento Monachil Aula de formación (fila 39 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Ayuntamiento Monachil
Aula de formación', 'P1813500D', 'Centro de formación las Canteras. C/ Leñadores , 30. Polígono Industrial las Canteras.18193 Monachil', '673366009', 'tecnicadeempleo@monachil.es', 'JOSE MORALES MORALES', '76422065B', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'ROCIO SANCHEZ MOLINA', '26042069C', NULL, '673 366 009', NOW(), NOW());

-- Ayuntamiento Otura (fila 40 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Ayuntamiento Otura', 'P1815200I', 'PLAZA DE ESPAÑA, 3 18630 OTURA(GRANADA)', '958555101', 'angelesvalladares@ayuntamientodeotura.es', 'NAZARIO MONTES PARDO', '44252314S', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'ANGELES VALLADARES DE LA VARGA', '09764295J', 'angelesvalladares@ayuntamientodeotura.es', '671639233', NOW(), NOW());

-- Ayuntamiento Padul (fila 41 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Ayuntamiento Padul', 'P1815300G', 'Av. de Andalucía, 66, 18640 Padul, Granada', NULL, 'programas@padul.org', 'Celia Villena De Francisco', '75147316Z', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Ruiz Ferrer, José Manuel', '44253391B', 'programas@padul.org', '670 63 40 60', NOW(), NOW());

-- Ayuntamiento Peligros (fila 42 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Ayuntamiento Peligros', 'P1815600J', 'Plaza Constitucion 1', '958400065', 'juventud@ayuntamientopeligros.es', 'Roberto C. García jimenez', '4264446A', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Alfredo Alvarez Luque', '24206971T', NULL, '650325576', NOW(), NOW());

-- Ayntamiento Torvizcón (fila 43 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Ayntamiento Torvizcón', 'P1818200F', 'Plaza AYUNTAMIENTO, 1, 18430 Torvizcón, Granada', NULL, 'torvizcon@dipgra.es', 'Juan David Moreno Salas', '24271285Y', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Jiménez Barros, Virtudes', '77767329M', 'torvizcon@dipgra.es', NULL, NOW(), NOW());

-- Beep-PCBlue (fila 45 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Beep-PCBlue', 'B67773101', 'Esquina con, Calle Camino de Ronda, 102, C. Infanta Beatriz, 3, 18004 Granada', '625072680', 'bgranada@beep.es', 'Álvaro Manuel Jerónimo Navarro', '75153004K', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Álvaro Manuel Jerónimo Navarro', '75153004K', NULL, NULL, NOW(), NOW());

-- Bidafarma (fila 48 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Bidafarma', 'F90280116', 'Calle Pino Central 38, Pol. Ind. El Pino, 41016 Sevilla', '954 44 97 96', 'practicas@bidafarma.es', 'Joaquín Argudo De La Cuadra', '31707333Q', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'ROPERO LOPEZ, MANUEL R', '34847903M', NULL, NULL, NOW(), NOW());

-- BIOS TECHNOLOGY SOLUTIONS S.L (fila 49 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('BIOS TECHNOLOGY SOLUTIONS S.L', 'B93209898', 'CARRETERA DE CORDOBA KM 429 NAVE Nº2 18230 ATARFE ( GRANADA)', '615 04 54 02', 'info@bios.es', 'JUAN DE DIOS FERNANDEZ SILLERO', '24260105G', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'JUAN DE DIOS FERNANDEZ SILLERO', '24260105G', 'jsillero@bios.es', '958204011 // 615045402', NOW(), NOW());

-- BON III DEL REGIMIENTO DE TRANSMISIONES 22 (GRANADA) (fila 50 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('BON III DEL REGIMIENTO DE TRANSMISIONES 22 (GRANADA)', 'S4100006H', 'C/SAN MATIAS Nº26 GRANADA', '958 218300', 'vidoy@et.mde.es', 'ANTONIO TEJEDA VELARDE', '53267476M', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'MIGUEL ANGEL VIDOY RUFINO', '23803285X', 'vidoy@et.mde.es', '958 218300', NOW(), NOW());

-- Bracelit (fila 52 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Bracelit', '---', 'C/ Puentezuelas, 3 18002 Granada', '663 32 65 05', 'jose@bracelit.es', '---', '---', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Braun Marketing & Consulting (fila 53 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Braun Marketing & Consulting', 'B19634443', 'C/Cuartelillo nº12 2ºD, 18009 Granada', '666 52 36 68', 'carlos@braunmarketingandconsulting.es', 'Carlos Martín Alguacil', '76438173L', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Maria del Carmen García Trinidad', '75150647X', 'carmen@braunmarketingandconsulting.es', '665649876', NOW(), NOW());

-- Cefinsur. Comunicaciones Electricidad y Fomentos del Sur (fila 54 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Cefinsur. Comunicaciones Electricidad y Fomentos del Sur', 'B18806018', 'AVDA. CARMEN MORCILLO, 2 LOCAL C.P. 18150 GOJAR (GRANADA)', '626399881', 'administracion@cefinsurfibra.com', 'ANTONIO FCO. MUÑOZ BENITEZ', '74651816A', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'ANTONIO FCO. MUÑOZ BENITEZ', '74651816A', 'administracion@cefinsurfibra.com', '626399881', NOW(), NOW());

-- CEIP Escultor César Molina Megías (fila 55 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('CEIP Escultor César Molina Megías', '24271917V', 'C. Venezuela, 2, 18194 Churriana de la Vega, Granada', '671 59 99 59', NULL, 'Francisco Morales Cervera', '24271917V', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Cellnet Tecom, S.L. (fila 56 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Cellnet Tecom, S.L.', 'B-19601129', NULL, '606180086', 'gerencia@yoigogranada.com', 'Juan Carlos Medina Fernandez', '44256387V', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Centro de documentación musical de Andalucía. Consejería de Cultura y Patrimonio Histórico. Junta de Andalucía. (fila 58 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Centro de documentación musical de Andalucía. Consejería de Cultura y Patrimonio Histórico. Junta de Andalucía.', 'S4111001-F', NULL, '600 14 31 13 (643113)
958 563 504', 'informacion.cdma.ccul@juntadeandalucia.es', 'Leandro Herranz Ortega', '07801897K', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Ignacio José Lizarán Rus', '24265549C', NULL, '600143113', NOW(), NOW());

-- Cívica (fila 60 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Cívica', 'B18866749', NULL, '958431780', 'info@civica-soft.com', 'JOSEP ROIG SALVADOR', '46354075J', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Miguel Ángel Medina López', '23806446C', NULL, '958431780', NOW(), NOW());

-- CLEMINSUR (GRUPOEI) (fila 61 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('CLEMINSUR (GRUPOEI)', 'B19697051', NULL, '958290053', 'info@estudioinformatica.com', 'Juan Antonio Cárdenas Martín', NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Climasol (fila 62 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Climasol', 'F18098202', NULL, '958121194', 'info@climasol.es', 'Alfredo Fernández Valdivieso', NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- CloseMarketing S.L. (fila 63 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('CloseMarketing S.L.', 'B19618909', NULL, NULL, 'sacra@closemarketing.es', 'Sacramento Jaimez Roman', NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Coinforge (fila 64 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Coinforge', 'B75789636', NULL, NULL, NULL, NULL, NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Colegio Cristo de la Yedra (fila 65 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Colegio Cristo de la Yedra', 'R1800116D', 'Paseo de Cartuja,2, 18011 Granada', '958160060
679349719', 'd.general.cristo@jesus-maria.net', 'Francisco Javier Lorite García', '26472497A', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Andrés Galán Díaz', '24262183N', NULL, NULL, NOW(), NOW());

-- Colegio Genil (CEIP) (fila 66 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Colegio Genil (CEIP)', 'S4111001F', 'Plaza General Emilio Herrera, Nº4 18005, Granada', '667408062
687737517', 'Gloria
José Antonio', 'Gloria López López', '74670959X', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'José Antonio Palma García', '75144815C', NULL, NULL, NOW(), NOW());

-- ControlSys (Jaén) (fila 69 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('ControlSys (Jaén)', 'B23437007', 'Calle Torredonjimeno 35, 23009 Jaén - Jaén', NULL, 'fanguita@controlsys.es', 'José Torres Criado', '25989093J', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Francisco Campos Herrera', NULL, 'fcampos@controlsys.es', '690 309 847', NOW(), NOW());

-- Cubicup Design Home S.L (fila 72 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Cubicup Design Home S.L', 'B40505893', 'SEDE DE GRANADA: Licenciado Vidriera 2, 18008, Granada', NULL, 'f.almenara@cubicup.com', 'Gabriel Cambra Revert OJO ESTA YA NO', '48291968H', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Fabián Almenara Cerezo', '14630199Z', 'f.almenara@cubicup.com', '665340115', NOW(), NOW());

-- Cuerva (fila 73 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Cuerva', 'b18210013', 'Calle Santa Lucía, 1K. 18194 Churriana de la Vega. Granada', '958 570360', 'info@cuervaenergia.com', 'IGNACIO CUERVA VALDIVIA', '24273409Z', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'RUBEN SANCHEZ IRUELA', '45714982K', NULL, NULL, NOW(), NOW());

-- CYMTECH SOLUCIONES TECNOLOGICAS (fila 74 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('CYMTECH SOLUCIONES TECNOLOGICAS', 'B19707264', 'Avd Andalucia, Edf Diputacion CIE Of. 11', '626 87 88 52', 'info@cymtech.es', 'Abel Carmona Esmeralda', NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Desarrollo TIC, S.L. (fila 76 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Desarrollo TIC, S.L.', 'B19559194', 'Calle Alhóndiga, 37. Planta 2 - Oficina 3, Granada (18001)', '958258535', 'administracion@desarrollotic.com', 'Antonio Rico Ortega', '44282679C', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Antonio Rico Ortega', '44282679C', 'administracion@desarrollotic.com', '958258535', NOW(), NOW());

-- Doctor. SEO (fila 78 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Doctor. SEO', '74737701Y', NULL, '958 095800
601 01 92 65', 'hola@doctorseo.es', 'Noel Barral Casarrubios', '74737701Y', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Marina Sanchez Rodriguez', '75145204H', NULL, NULL, NOW(), NOW());

-- EMPETEL SERVICIOS SL (fila 85 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('EMPETEL SERVICIOS SL', 'B44896397', 'C/ EMPERATRIZ EUGENIA, 17', '958992709/ 958277014
623 20 35 99', 'info@empetel.es', 'OMAR ARROUD EL MASMOUDI', '77968695Y', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Navas Peña, Juan Antonio', '75570949X', NULL, NULL, NOW(), NOW());

-- ESCOA Formación (fila 88 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('ESCOA Formación', 'B05455027', 'Av. de la Fuente, 34, 18210 Peligros, Granada', NULL, 'info@escoaformacion.es', 'Jose Miguel Moreno Hita', '44277252K', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Fabiola Ávila Rubio', '75142730M', NULL, '625 96 05 91', NOW(), NOW());

-- Estudio Nemo (ALFONSO JURADO MESA) (fila 89 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Estudio Nemo (ALFONSO JURADO MESA)', '74641177J', 'C/PABLO PICASSO, Nº 37 PULIANAS, GRANADA, CP 18197', '695395766', 'alfjuradomesa@gmail.com', 'ALFONSO JURADO MESA', '74641177J', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Euroinnova Business School S.L (es Innoforma y iEditorial) (fila 90 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Euroinnova Business School S.L (es Innoforma y iEditorial)', 'B19521574', 'Camino  de  la  Torrecilla  N.º  30,  Edificio  Educa  Edtech,  Oficina  28,  C.P.  18.200, Maracena (Granada).', '958 050 225', NULL, 'Jesús Moreno Hidalgo', '46955861G', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Daniel Cabrera Armenteros', '75017069Q', 'todo a traves de RR.HH', 'todo a traves de RR.HH', NOW(), NOW());

-- Eventos Vértigo (fila 91 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Eventos Vértigo', 'B93282077', 'AVENIDA MALAGA, 2. 18210. Peligros. Granada', NULL, NULL, 'Antonio Cabrera Torres', '74831341J', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Antonio Cabrera Torres', '74831341J', NULL, NULL, NOW(), NOW());

-- Experian (Antiguo Axesor) (fila 93 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Experian (Antiguo Axesor)', 'A18413302', NULL, '679 019 515
858 70 93 86
958750500', 'rherrera@axesor.es 
Yolanda.Jimenez@experian.com', 'Carlos García Castillo', '44250699X', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Rubén Herrera Ruiz', '75128507L', NULL, NULL, NOW(), NOW());

-- Facultad Relaciones Laborales (fila 94 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Facultad Relaciones Laborales', 'Q1818002F', 'Calle Rector López Argüeta, s/n, 18071 Granada', '958 24 43 94', 'marilena@ugr.es', 'Maria del Mar Holgado Molina', '24243650V', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Maria Magdalena Jimenez Barrionuevo', NULL, 'marilena@ugr.es', '659 21 74 54', NOW(), NOW());

-- FEGRADI COCEMFE GRANADA (fila 95 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('FEGRADI COCEMFE GRANADA', 'G18075408', 'Paseo Laguna Cameros 6, 18008 Granada.', '958 12 34 35', 'rogaol@fegradi.es', 'Marta Castillo Diaz', '74656323W', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Antonio de Haro Bueno', '44272169K', NULL, NULL, NOW(), NOW());

-- Festival Internacional de Musica y Danza de Granada (fila 97 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Festival Internacional de Musica y Danza de Granada', 'P6808903F', NULL, '672 212 586 (informática)', 'info@granadafestival.org', 'Elena Cazorla García', '24230788N', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Ismael Padilla Matas', '44259736P', NULL, NULL, NOW(), NOW());

-- FIDESOL: Fundación i+d del software libre (fila 98 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('FIDESOL: Fundación i+d del software libre', 'G18799874', 'Parque Tecnológico de Ciencias de la Salud, Av. Innovación, Edificio BIC Granada, 18100 Armilla, Granada', '958 750 457
625 934 993', 'eromero@fidesol.org', 'Juan Manuel Lombardo Enríquez', '24274207F', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Fernández Rosales, Celia', '76438038E', NULL, NULL, NOW(), NOW());

-- Gaming Project (fila 102 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Gaming Project', '75155258K', 'Camino de Ronda 151, Local, 18003', '667554646
624601144', 'gamingproject.es@gmail.com', 'Fco Javier Justicia Valdivia', '75155258K', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Fco Javier Justicia Valdivia', '75155258K', 'gamingproject.es@gmail.com', '667554646', NOW(), NOW());

-- Garaje de Ideas S.L (fila 103 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Garaje de Ideas S.L', 'B86426780', 'Ps Castellana 95, 15, 28046, Madrid', '910 519 490', 'people.espana@garajedeideas.com', 'ALBERTO BARRIO VICENTE', '02666996P', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Rubén Muñoz Ruiz', '74692374N', 'ruben@codigonexo.com', '633801535', NOW(), NOW());

-- Gestión Unificada de Fertilizantes S.L. (fila 104 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Gestión Unificada de Fertilizantes S.L.', 'B18782730', NULL, '606609525', 'jros@herogra.com', 'Juan Romero Ruiz', '24116665S', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Jesús Ros Pérez', '24214225D', NULL, '606 609 525', NOW(), NOW());

-- Gestioniza Digital Service (fila 106 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Gestioniza Digital Service', 'B09622861', 'C/ Navalcarnero, 69 - 28939 Arroyomolinos (Madrid)', '621 244 323', 'rrhh@gestioniza.com
pablo.garcia@gestioniza.com', 'Juan Valdepeñas Jimeno', '06261674Q', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'RUBÉN VILLANUEVA ORTEGA', '42213384N', 'ruben@gestioniza.com', NULL, NOW(), NOW());

-- GETECOM (Generación Tecnológica de Comunicaciones SL) (fila 107 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('GETECOM (Generación Tecnológica de Comunicaciones SL)', 'B13590534', 'Polígono industrial del Henares. C/ Livorno 60, portal B 2º. Marchamalo. 19180 Guadalajara', '621228591', 'jaime.fuentes@getecom.es', 'Jonathan Matas Gómez', '05671902X', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Jaime Fuentes Pérez', '26044072E', NULL, '621228591', NOW(), NOW());

-- Global Conely SL (fila 109 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Global Conely SL', 'B18649814', 'Avda Pradollano 24, pol ind Sierra Nevada
18339 CIJUELA (Granada)', '958416503 - 658771675', 'info@conely.es', 'Juan José Chacón Lacalle', '74646600P', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Francisco Javier Arco Navarro', '75926360W', NULL, '615362395', NOW(), NOW());

-- Gloss Print. Monkey Repara (fila 110 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Gloss Print. Monkey Repara', 'Z1191264F', 'C. Granada, 18, Local 4, 18151 Ogíjares, Granada', '624535737', 'oscarmanzanoarias@gmail.com', 'Óscar Manzano Arias', NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Óscar Manzano Arias', NULL, NULL, '672961253', NOW(), NOW());

-- GranaDev SL (fila 111 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('GranaDev SL', 'B19647700', 'C/ Buensuceso 39, Granada', '958 207 123
669245258', 'me@raul-alvarez.es', 'Raul Álvarez Hinojosa', '26045090M', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Raul Álvarez Hinojosa', '26045090M', NULL, NULL, NOW(), NOW());

-- Greening Group (fila 114 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Greening Group', 'A19714559', 'C/ Alcayata 4, Polígono El florío. Granada', '605426754', NULL, 'Manuel Mateos Palacios', '74690446Q', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Pablo Palacios Rubio', '75146440N', NULL, '653522855', NOW(), NOW());

-- GRIDEA MARKETING SL (fila 115 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('GRIDEA MARKETING SL', '76654897Z
B09655242 ??', 'CALLE NAZARÍES 4, BAJO IZQUIERDA. 18005 - GRANADA', '623360049
623355678', 'hola@gridea.es', 'FRANCISCO CARACCIOLO MENOYO LEÓN', '76654897Z', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'JESÚS LÓPEZ LÓPEZ', '14273977Q', 'jesus@gridea.es', '623 35 56 78', NOW(), NOW());

-- GSI Microdigital (fila 118 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('GSI Microdigital', 'B18622258', 'C/Playa Santiago nº 4, 18014, Granada', '(+34) 958 204 228
667444572', 'info@microdigital.es
fede@microdigital.es', 'Juan Federico Molina Hervás', '24202220X', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Jorge Rojas', '44262732z', NULL, '667444573
677444572', NOW(), NOW());

-- Holaseo Marketing (fila 120 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Holaseo Marketing', '74641092C', NULL, NULL, 'info@holaseo.es', 'Raúl Sánchez Palacios', NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Horizontes Informatica (fila 121 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Horizontes Informatica', 'B18082297', NULL, '958535618', 'info@horizontes-informatica.com', 'Clements, Willem', 'X0542703H', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Vallejos Chacón, Víctor Hugo', '77553284K', NULL, NULL, NOW(), NOW());

-- H. Santos D, S.L. (HOTEL SARAY) (fila 122 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('H. Santos D, S.L. (HOTEL SARAY)', 'B02826881', 'Calle Prof. Tierno Galván, 4, Zaidín, 18006 Granada', '958 13 00 09
(+34) 958 050 487', 'juana.hoyo@hsantos.es', 'Francisco Caba Sanchez-Migallon', '51834874G', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Marcos Fuentes López', '77553476Y', NULL, NULL, NOW(), NOW());
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Fernando Tofé Navarro', '45593333L', 'fernando.tofe@hsantos.es', '958050462', NOW(), NOW());

-- Iconic Midcentury (fila 123 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Iconic Midcentury', '38853912N', 'Calle Salmón, nº9. 18015. Granada.', '621093692', 'info@iconicmidcentury.com', 'Manuel García Fernández', '38853912N', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Idento (fila 124 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Idento', 'B18893578', 'Edificio San Isidro, C. Graham Bell, 3, Planta Baja, Puerta 2, 18100 Granada', '958 199 777', 'blopezmezquita@idento.es', 'Baldomero López-Mezquita Torres', '74674264A', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Francisco Javier Mercado', NULL, 'fmercado@idento.es', NULL, NOW(), NOW());

-- Incorpora Márketing SL (fila 129 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Incorpora Márketing SL', 'B18977207', 'Edificio Forum', '958309967
645841800', 'programacion@incorporamarketing.es', 'CHRISTIAN PRADOS SANCHEZ', '44282880Z', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'JOSE PEDRO LOPEZ BARBOSA', '74671811B', 'programacion@incorporamarketing.es', '722 73 74 45
958 30 99 67', NOW(), NOW());

-- Industrias Lácteas de Granada - Lactalis (fila 130 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Industrias Lácteas de Granada - Lactalis', 'B18557264', 'PULEVA-Camino de Purchil 66, 18004 Granada', '958240100
649397775', 'rrhh@lactalispuleva.es', 'Sonia Muriel', '28761823R', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Enrique Zazo Martín', '09302449F', NULL, '649397775', NOW(), NOW());

-- INFINICABLE TELECOMUNICACIONES S.L. (fila 131 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('INFINICABLE TELECOMUNICACIONES S.L.', 'B72610264', 'CENTRO DE TRABAJO: Calle Jardines, 7, Otura', '958198500 - 633409396', 'fatima.ortiz@fibergy.es', 'DAVID ROBLES FERNANDEZ', '74689226S', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'JOSE IGNACIO IZQUIERDO MARTIN', '14631318Y', NULL, '722176796', NOW(), NOW());

-- Informática Megasur (fila 133 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Informática Megasur', 'B18314112
24174303S', 'Parque Metropolitano, C/ Los Visos 14, CP 18130 , Escuzar Granada', '958 50 90 10', 'fizquierdo@megasur.es', NULL, NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Jose Garrido Ledesma
Ismael Gómez
Irene Izquierdo', '24174731Y', 'jgarrido@megasur.es
igomez@megasur.es
iizquierdo@megasur.es', NULL, NOW(), NOW());

-- INFOTEL HUETOR TAJAR, S.L.U (fila 135 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('INFOTEL HUETOR TAJAR, S.L.U', 'B18411280', 'C/ ANCHA, 60, Huetor Tajar', '654392322
958 32 53 69', 'infotel@infotelht.es', 'FRANCISCO JOSE CAMPAÑA JIMENEZ', '24232322M', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'FRANCISCO JOSE CAMPAÑA JIMENEZ', NULL, NULL, NULL, NOW(), NOW());

-- infowork.es (fila 136 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('infowork.es', 'B18645481', 'Parque Empresarial Sur, Nave 3 (Ctra. Córdoba), 18015 - Granada.', '958016124', 'klopez@infowork.es', 'JOSE ANTONIO PAIZ RODRIGO', '44256656X', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'LUIS ALBERTO CASTRO TURRIÓN', '44271366T', NULL, '958016131', NOW(), NOW());

-- INNOFORMA ELEARNING TECHNOLOGIES SL (Consultora de RRHH) gestiona el acceso a empresa: Innoforma, iEditorial, Euroinnova Formación (fila 137 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('INNOFORMA ELEARNING TECHNOLOGIES SL
(Consultora de RRHH) gestiona el acceso a empresa: Innoforma, iEditorial, Euroinnova Formación', 'B18915140', 'ABETO, EDIF.CEG, FASE II 25, Atarfe-18230, Granada, Centro de Empresas Granada, parque empresarial La Ermita', '958 050 225
958050215', 'rrhh@indize.es', 'Francisco Javier Moreno Hidalgo', '46950860V', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Jesus Gutierrez Osborn', '44289354W', NULL, NULL, NOW(), NOW());

-- Innovaciones Metacalorificas SL (fila 138 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Innovaciones Metacalorificas SL', 'B18789149', 'Calle Argentina, nº90. 18360. Huétor Tájar. GRANADA', '958333789', 'contabilidad@inmecal.com', 'José Ignacio Toro Cuberos', '74638020F', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'José Ignacio Toro Cuberos', '74638020F', 'contabilidad@inmecal.com', '695945884', NOW(), NOW());

-- IntegrateMedia (fila 141 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('IntegrateMedia', 'F19686328', NULL, NULL, 'angel@integratemedia.es', 'Luis Jesús Sancho Jáimez', '75158615C', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'José María Sancho Jáimez', '75135330B', NULL, NULL, NOW(), NOW());

-- Intelligenia SA (fila 142 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Intelligenia SA', 'B18867481', NULL, '958077070', 'info@intelligenia.com', 'José Carlos Calvo Tudela
651615711', '74695942S', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'José Antonio Ruiz Rodriguez', '26246214V', NULL, NULL, NOW(), NOW());

-- Itronic (fila 146 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Itronic', 'E19577006', 'Paseo Jardín de la Reina Nº6 local 6. 18006 Granada', '958040576
654344320', 'itronic.es', 'JOSE PEDRO SOLA TRIGUERO', '74233332G', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, '74233332G', NULL, NULL, '74233332G', NOW(), NOW());

-- Jaime Jiménez Titos S.L. (TitosConsulting) (fila 147 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Jaime Jiménez Titos S.L. (TitosConsulting)', 'B19663806', NULL, '958997017', 'info@titosconsulting.com', 'Jaime Jiménez Titos', NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Jose Pérez Aguilar (fila 148 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Jose Pérez Aguilar', '73200499D', 'avenida d''es castell, 6  - Menorca, es castell', '655839037', 'herreroescastell@gmail.com', 'Jose Pérez Aguilar', '73200499D', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Jovitelsat (fila 149 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Jovitelsat', 'B18611087', 'Santa Clotilde,6 18006 Granada(Granada)', '958295600', 'ecarrizo@jovitelsat.es', 'Encarnación Carrizo Oliva', '29079642Y', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Manuel Moreno Rey', '75151472F', 'lolo040685@gmail.com', '661164166', NOW(), NOW());

-- KodoPeople (Behave 4 Management) (fila 151 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('KodoPeople (Behave 4 Management)', 'B19650092', 'Avda. del Conocimiento 41 Centro de empresas PTS, Oficina A204', '958 414 297', 'admin@kodopeople.com', 'Pedro Nolasco Campos Godoy', '24237110D', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Antonio Manuel Espín Martín', '74648214N', 'amespin@kodopeople.com', '660634041', NOW(), NOW());

-- LAUAMA RC, S.L (Loja) (fila 153 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('LAUAMA RC, S.L (Loja)', 'B18436840', 'AVD. DE ANDALUCÍA, 40 BAJO 18300 LOJA GRANADA', '958321819', 'lauamarc@gmail.com', NULL, NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- LUBRIME MEYGO S.L. (fila 154 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('LUBRIME MEYGO S.L.', 'B87439741', 'C/ Almuñecar, Parcela R-171 Polígono Industrial Juncaril 18220 - Albolote (Granada)', '958 41 41 97', 'info@lubrimemeygo.com', 'MANUEL Fº JIMÉNEZ FERNANDEZ', '74660327G', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'MANUEL Fº JIMÉNEZ FERNANDEZ', '74660327G', 'manuel@lubrimemeygo.com', '619101663', NOW(), NOW());

-- M&B CODEKO DIGITAL WORKSHOP SL (fila 155 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('M&B CODEKO DIGITAL WORKSHOP SL', 'B19625086', 'C/ Lepanto 5B, 18110, Las Gabias', '810101202 
958254330
610225003', 'info@codeko.com', 'María Hinojosa Gutiérrez', '74681615V', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Boris Burgos', '74683642C', 'bburgos@codeko.com', '810101202', NOW(), NOW());

-- Maniacs informática (fila 156 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Maniacs informática', 'En Gabia', NULL, '611 04 87 25', NULL, NULL, NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Masion Games (fila 157 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Masion Games', 'B19714237', NULL, '690 22 06 66', 'contacto@masiongames.com', NULL, '75139714W', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- MOKA comunicacion y Marketing SL (fila 160 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('MOKA comunicacion y Marketing SL', 'B19653518', NULL, '655927484', 'hola@mokacomunicacion.com', 'Victor Molina Angel', NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Victor Jose Molina Angel', '74654680s', NULL, NULL, NOW(), NOW());

-- Móvil Xpress (fila 161 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Móvil Xpress', '75573293P', 'Avenida de la Sierra, 45. 18008. Cenes de la vega. Granada', '662551004', 'info@mobilexpress.es', NULL, NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- MONKEY NETWORKS SL (fila 162 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('MONKEY NETWORKS SL', 'B18783480', 'Calle Camino Bajo de Huétor, 73, 18008 Granada.', '910 382 839', 'contacto@grupomotiva.es', 'Juan María Fernández Fernández', '24277043Z', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Fernández Fernández, Juan María', '24277043Z', NULL, NULL, NOW(), NOW());

-- Mulconsa, Edificación y Civil SLU (fila 163 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Mulconsa, Edificación y Civil SLU', 'B18997288', 'C/ Puerta Real de España, 1. 4ºA. 18009. Granada', '958210475', 'info@mulconsa.es
(enviar convenido a documentacion@mulconsa.es)', 'Victoriano Moreno Fuentes', '44275040V', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Javier Jiménez Cesar', '74647125G', 'j.jimenez@mulconsa.es', NULL, NOW(), NOW());

-- MULHACEN SOFT S.L. (fila 164 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('MULHACEN SOFT S.L.', 'B01617851', 'C/ Martinez Campos 20, 7-i, 18002, Granada', '660753421', 'info@mulhacensoft.com', 'Diego Castilla Rodríguez', '75140358W', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Cristina Muñoz Manrique', '74726863R', NULL, '660753421  958.56.10.56', NOW(), NOW());

-- NANOBYTES INFORMÁTICA Y TELECOMUNICACIONES S.L (fila 165 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('NANOBYTES INFORMÁTICA Y TELECOMUNICACIONES S.L', 'B19525138', 'Calle Melilla nº 2 Primera Planta, Churriana de la Vega (Granada) CP: 18194', '958574702', 'rrhh@nanobytes.es', 'Bernardo Ruiz Monge', '74690405K', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Ionel Lazar Lazar', 'X9952564G', 'ilazar@nanobytes.es', '958574702', NOW(), NOW());

-- Nazaríes Innovation Technologies (fila 167 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Nazaríes Innovation Technologies', 'B18912659', 'C/ Parque de las ciencias Nº1', '659718191
958996246', 'violeta.haro@nazaries.com', 'Eduardo Haro Amate', NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- NoSoloPc (fila 172 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('NoSoloPc', 'B18971481', 'Avenida Luis Miranda Dávalos, nº4, -18014 Granada', '958162661 / 679101277', 'info@informaticanosolopc.com', 'PABLO MARTINEZ GUIRADO', '75144469L', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'CARLOS ABELLAN URENDEZ', '76627371L', 'info@informaticanosolopc.com', '95862661', NOW(), NOW());
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'José Sánchez Mingorance', '50750884G', 'telefonia@informaticanosolopc.com', '95862661', NOW(), NOW());

-- NTT Data Spain Centers, S.L.U. (fila 174 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('NTT Data Spain Centers, S.L.U.', 'B84861178', 'c/ Recogidas 37, Granada', '958 52 66 52', 'david.uribepacheco@nttdata.com', 'José Antonio Bermúdez Ruíz', '34798320X', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- OFFICE & LAPTOP SLU (fila 176 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('OFFICE & LAPTOP SLU', 'B19605765', 'C ANCHA 7 HUETOR TAJAR - REDONDA NORTE 100 HUETOR TAJAR', '675837178', 'distribucionesofimaticasht@gmail.com', 'ANTONIO RAUL NOGALES PEREZ', '75137709K', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Omega CRM Consulting (fila 177 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Omega CRM Consulting', 'B18705087', 'C/Periodista
Fernando Gómez de la Cruz, 61, Oficina 9 Granada, CP 18014', '663041319', 'seleccion@omegacrmconsulting.com', 'José Luis Molina Ruiz', NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Onice (fila 178 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Onice', 'B19608223', 'Este 4', '639965693', 'onice@onicom.es', NULL, NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Opcionalia Soluciones Avanzadas S.R.L. (fila 180 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Opcionalia Soluciones Avanzadas S.R.L.', 'B18863167', NULL, '958 13 12 51
902 35 38 35', 'info@opcionalia.com', 'Antonio Palos Cid-Fuentes', NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Papayacore Software Engineering SL (fila 182 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Papayacore Software Engineering SL', 'B01873181', 'C/ Granada 1, 2b. Las Gabias. Granada', '651 88 20 80', 'jmartin@papayacore.com', 'Jose Antonio Martin Martín', NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- PC Express Informática (fila 184 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('PC Express Informática', '76438509X', 'C/Martinez Campos', '678269939', 'soporte@pc-express.es', 'Álvaro Meseguer Cabrera', '76438509X', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Álvaro Meseguer Cabrera', '76438509X', 'soporte@pc-express.es', '630546481', NOW(), NOW());

-- Persone Solutions S.L. (fila 185 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Persone Solutions S.L.', 'B44687598', 'C. San Antón, 72, Oficina 40, Ronda, 18005 Granada', '958 46 00 90', 'gestion@persone.chat', 'Juan Francisco Serván Cerván', '42869372V', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Francisco Mirasol Romero (CEO)', '74657484J', 'paco@persone.chat', '690667781', NOW(), NOW());

-- PWC (Pricewaterhousecoopers Asesores de Negocios SL) Granada (fila 187 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('PWC (Pricewaterhousecoopers Asesores de Negocios SL) Granada', 'B78016375', 'C/ Abeto, s/n, 18230 Atarfe, edificio centro de empresas Granada', '679 91 35 00', 'gomez.amor.antonio@pwc.com', 'Estíbaliz Pérez Aramburu', '44160779C', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Gómez Amor, Antonio José', '74887272P', 'gomez.amor.antonio@pwc.com', '679 91 35 00', NOW(), NOW());

-- R.G. Electricidad (fila 193 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('R.G. Electricidad', '44289541M', 'Placeta Río Jerez, número 3,Cullar Vega, 18195', '670898025', 'ricargranada76@gmail.com', 'Ricardo Gualda Moreno', '44289541M', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Ricardo Gualda Moreno', '44289541M', 'ricargranada76@gmail.com', '670898025', NOW(), NOW());

-- RPV Recursos Punto de Venta (fila 194 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('RPV Recursos Punto de Venta', 'B80472913', 'Avda de la Industria, 25, Pol.Ind. La Cantueña Fuenlabrada', '915268064', 'jonayarmas@rpv.es', 'Marisol de Barrio García', '09768539W', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Rynder Tec S.L. (fila 195 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Rynder Tec S.L.', 'B13867544', 'Paseo de Colón 40, local 4. 18220
Albolote', '958 99 97 33/744 72 78 90', 'ryndertec@gmail.com', 'Bryan Alexander Gaton Avalo', 'Y0274142L', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Bryan Alexander Gaton Avalo', 'Y0274142L', NULL, '958 99 97 33/744 72 78 90', NOW(), NOW());

-- Servicio Técnico dab (fila 196 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Servicio Técnico dab', '25711955w', 'Golondrina 34 . Local A Las Gabias . 18110', '693 864 638', 'tallerdab@gmail.com', 'David Aurelio Arrebola Borrego', NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'David Aurelio Arrebola Borrego', '25711955w', 'tallerdab@gmail.com', '693 864 638', NOW(), NOW());

-- SISTEMAS INFORMATICOS DE NEGOCIO,S.L (SIDN) (fila 198 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('SISTEMAS INFORMATICOS DE NEGOCIO,S.L (SIDN)', 'B-B18753590', 'GRAN VIA DE COLON Nº 8 2º PLANTA, C.P 18010, GRANADA', '958 049 153', 'raul.velasco@sidn.es / fabiola.leyva@sidn.es', 'JESUS MARIA MOYA GARCIA-LUJAN', '11835857B', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Darío Torres Marín', '20080665D', 'dario.torres@sidn.es', '722508586', NOW(), NOW());

-- Sistemas y Automatismos Agrarios de España SL (fila 199 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Sistemas y Automatismos Agrarios de España SL', 'B19654615', NULL, '600249424', 'info@gpsagricola.com', 'Jose Antonio Vilchez Lopez', '44291147 R', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Jose Antonio Vilchez Lopez/ Ignacio', NULL, NULL, NULL, NOW(), NOW());

-- Servicopy (fila 202 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Servicopy', 'B18986521', NULL, '958 466 664', 'sergio@servicopy.es', 'J. Sergio Villegas Herrera', '44284548A', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'David Suarez Ortega', '44279314J', NULL, NULL, NOW(), NOW());

-- Skydev (fila 203 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Skydev', '60864655P', NULL, '612229412', 'skydevent@gmail.com', NULL, NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- SPS-Sport (fila 205 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('SPS-Sport', '24268183D', NULL, '958817190', 'david@sps-sport.com', 'David Francisco Rodriguez Sanchez', '24268183D', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- StartIdea (fila 206 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('StartIdea', 'B19583632', 'C/Conde Cifuentes  33, 18005-Granada', '627305162
958 045 789', 'somos@startidea.es', 'Mario Pablo Sánchez Barrón', '76440886H', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Mario Pablo Sánchez Barrón', '76440886H', NULL, '627305162', NOW(), NOW());

-- T-System IBERIA (fila 208 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('T-System IBERIA', 'A81608077', 'Avenida del Conocimiento 41, 18016 Granada', NULL, 'alexandra.reguera-gonzalez@t-systems.com', 'Miguel Blanca Dieste', 'Y5347351A', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Diego Suarez García', '50860023P', NULL, NULL, NOW(), NOW());

-- TECNOSECOND S.L.U (fila 209 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('TECNOSECOND S.L.U', 'B19572858', 'AVENIDA BLAS DE OTERO 10 LOCAL, maracena', '958421418 // 693844845', 'info@tecnosecond.com', 'MANUEL SALAS BURRUEZO', '74675184A', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'MANUEL SALAS BURRUEZO', '74675184A', NULL, '693844845', NOW(), NOW());

-- Telmi Telecom S.L. (fila 210 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Telmi Telecom S.L.', 'B19567999', 'C/A Coruña 16 - Pol. Tecnológico Ogíjares (Granada) - 18151', '958193737', 'cizquierdo@telmi.es', 'Juan Antonio Mañas Vela', '75097249H', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Juan Antonio Mañas Vela', '75097249H', NULL, '673552864', NOW(), NOW());

-- Terceto Comunicación SL (fila 211 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Terceto Comunicación SL', 'B19607142', NULL, '958405998', 'administracion@terceto.es', NULL, NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- TERTIUS INFORMÁTICA S.L. (fila 212 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('TERTIUS INFORMÁTICA S.L.', 'B19505098', 'C/ Los Puentes, 1 Bloque 1 1º H, Vegas del Genil', '679 019 515
858 70 93 86
958 22 25 08 958535618', 'administracion@i-tertius.es', 'Jose Luis Aguilar Alonso', '24247409G', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Jose Luis Aguilar Alonso', '24247409G', NULL, 'Sonia 679019515
José Luis 722304632', NOW(), NOW());

-- TM DIGITAL GRANADA (fila 213 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('TM DIGITAL GRANADA', 'B18516187', 'Calle Honda de Realejo, 18009 Granada', '958 04 99 99
625 35 00 96', 'info@tmdigital.es', 'Antonia Rueda', '24156293Z', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Claudia /Andrés Gamarra Vengoechea (Y9086215X)', '76441849S', '722398627 (Ándres)', NULL, NOW(), NOW());

-- Trevenque Sistemas de Información SL (fila 214 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Trevenque Sistemas de Información SL', 'B18316828', NULL, '958217229
958183030 (centralita)', 'josea@trevenque.es
aguerrero@trevenque.es', 'Buenaventura Porcel Jiménez', '24224728R', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'David Comino Mateos', '76086820Z', NULL, NULL, NOW(), NOW());
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Jose Javier López Yurda', '43826149V', NULL, NULL, NOW(), NOW());
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Pedro Reyes Raez', '75098885K', 'preyes@trevenque.es', NULL, NOW(), NOW());
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Miguel Angel López Ortega', '24210402G', 'miguelangel@trevenque.es', NULL, NOW(), NOW());
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Enrique Cartamil Cardeñoso', '77024606K', NULL, NULL, NOW(), NOW());

-- Trey Kilobytes de Soluciones SL (fila 215 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Trey Kilobytes de Soluciones SL', 'B19516772', NULL, '958109015', 'administracion@trey.es', 'Jorge Camacho Hernández', NULL, 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- UBUNET TELECOM S.L (fila 216 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('UBUNET TELECOM S.L', 'B19501295', 'AVDA FERNANDO DE LOS RIOS 11, EDIF 3, OFICINA 16', '958 99 38 03
677958958', 'info@ubunet.es', 'DAVID CASCALES AYBAR', '74682141Z', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'DAVID CASCALES AYBAR', '74682141Z', NULL, '677958958', NOW(), NOW());

-- Venturi Technologies (fila 219 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Venturi Technologies', '74741971K', 'c/ Moraledas nº4 Lanjarón', '633228572', 'juanca@venturi.ag', 'Chaves Puertas, Juan Carlos', '74741971K', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Chaves Puertas, Juan Carlos', '74741971K', 'juanca@venturi.ag', '633228572', NOW(), NOW());

-- VITHAS HOSPITALES, S.L.U (fila 224 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('VITHAS HOSPITALES, S.L.U', 'B36787844', 'avda. santa maría de la alhambra nº6 18008 granada', NULL, NULL, 'JOSE LUIS SALCEDO LAGULLON', '04549718L', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'ANTONIO PALOMO FRANCO', '74726792E', 'PALOMOFA@VITHAS.ES', '653460325', NOW(), NOW());

-- VIVAFIBRA TELECOMUNICACIONES S.L. (fila 225 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('VIVAFIBRA TELECOMUNICACIONES S.L.', 'B18952416', 'Cuesta Campos, 6, Bajo
18300 - Loja (Granada)', '958198500 - 633409396
663333488', 'fatima.ortiz@fibergy.es', 'CARLOS MARÍA ORTIZ CAMPILLOS', '74662532R', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'MARIA FATIMA ORTIZ CAMPILLOS', '75143641L', NULL, '663333488', NOW(), NOW());

-- Whitewall Solutions S.L (fila 229 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Whitewall Solutions S.L', 'B18982967', 'C/ Cedro s/n Nave 6, Pol. Ind. La Ermita, 18230 Atarfe, Granada', '958 04 29 73', 'jgonzalez@whitewallsolutions.com', 'José Manuel González López', '74723201L', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();

-- Winecta (App&Web) (fila 230 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Winecta (App&Web)', 'B19572346', NULL, '649 99 47 36', 'samuelst@appandweb.es', 'Samuel Sánchez Talavera', '76423145X', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'Rubén Sánchez Reche', '45718452H', NULL, NULL, NOW(), NOW());

-- Zona de ficción (fila 234 del listado)
INSERT INTO empresas (nombre, cif, direccion, telefono, email, responsable_nombre, responsable_dni, is_active, created_at, updated_at) VALUES ('Zona de ficción', 'B18995696', 'C/ Santa Aurelia N5, 1ºB', '648753326', 'info@zonadeficcion.com', 'J. Axel García Chacón', '75133632S', 1, NOW(), NOW()) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id);
SET @empresa_id = LAST_INSERT_ID();
INSERT INTO tutores_laborales (empresa_id, nombre, dni, email, telefono, created_at, updated_at) VALUES (@empresa_id, 'J. Axel García Chacón', '75133632S', NULL, NULL, NOW(), NOW());

COMMIT;
