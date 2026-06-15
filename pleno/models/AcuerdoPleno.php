<?php

use App\Config\Database;

class AcuerdoPleno
{
    private $conn;

    public function __construct($conn = null)
    {
        if ($conn instanceof \PDO) {
            $this->conn = $conn;
            return;
        }

        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function listarPorSesion(int $idSesion): array
    {
        if ($idSesion <= 0) {
            return [];
        }

        $stmt = $this->conn->prepare(
            "SELECT
                a.id_acuerdo,
                a.id_sesion,
                a.id_sesion_plenaria_tema,
                a.punto_numero,
                a.titulo_punto,
                a.texto_acuerdo,
                a.estado,
                a.usuario_creador,
                a.usuario_actualizador,
                a.fecha_creacion,
                a.fecha_actualizacion,
                a.fecha_eliminacion,
                TRIM(CONCAT(uc.pNombre, ' ', COALESCE(NULLIF(uc.sNombre, ''), ''), ' ', uc.aPaterno, ' ', uc.aMaterno)) AS creador_nombre,
                TRIM(CONCAT(ua.pNombre, ' ', COALESCE(NULLIF(ua.sNombre, ''), ''), ' ', ua.aPaterno, ' ', ua.aMaterno)) AS actualizador_nombre
             FROM pleno_acuerdos a
             LEFT JOIN t_usuario uc ON uc.idUsuario = a.usuario_creador
             LEFT JOIN t_usuario ua ON ua.idUsuario = a.usuario_actualizador
             WHERE a.id_sesion = :id_sesion
               AND a.estado = 'vigente'
             ORDER BY a.fecha_creacion ASC, a.id_acuerdo ASC"
        );
        $stmt->execute([':id_sesion' => $idSesion]);
        $acuerdos = $stmt->fetchAll();

        return is_array($acuerdos) ? $acuerdos : [];
    }

    public function listarPuntosSesion(int $idSesion, array $sesion = []): array
    {
        if ($idSesion <= 0) {
            return [];
        }

        $puntosCuentaGobernador = [];
        $puntosCuentaComisiones = [];
        $puntosVarios = [];
        $puntosFijos = [
            [
                'id_sesion_plenaria_tema' => null,
                'punto_numero' => '1',
                'titulo_punto' => 'Aprobacion de Acta: ' . (trim((string)($sesion['aprobacion_actas'] ?? '')) ?: 'Sin acta registrada'),
            ],
            [
                'id_sesion_plenaria_tema' => null,
                'punto_numero' => '2',
                'titulo_punto' => 'Cuenta Presidente del Consejo Regional',
            ],
            [
                'id_sesion_plenaria_tema' => null,
                'punto_numero' => '3',
                'titulo_punto' => 'Cuenta Comisiones',
            ],
            [
                'id_sesion_plenaria_tema' => null,
                'punto_numero' => '4',
                'titulo_punto' => 'Varios',
            ],
        ];

        $stmt = $this->conn->prepare(
            "SELECT
                spt.id,
                spt.tipo_punto,
                spt.nombre_imprevista,
                spt.titulo_punto,
                spt.seccion_orden,
                spt.orden,
                c.nombreComision,
                t.nombreTema
             FROM sesion_plenaria_temas spt
             LEFT JOIN t_comision c ON c.idComision = spt.id_comision
             LEFT JOIN t_tema t ON t.idTema = spt.id_tema
             WHERE spt.id_sesion = :id_sesion
               AND spt.vigente = 1
             ORDER BY COALESCE(NULLIF(TRIM(spt.seccion_orden), ''), 'varios') ASC, spt.orden ASC, spt.id ASC"
        );
        $stmt->execute([':id_sesion' => $idSesion]);

        while ($row = $stmt->fetch()) {
            if (!is_array($row)) {
                continue;
            }

            $puntoNormalizado = [
                'id_sesion_plenaria_tema' => (int)$row['id'],
                'punto_numero' => $this->resolverNumeroPunto($row),
                'titulo_punto' => $this->resolverTituloPunto($row),
            ];

            $seccion = trim((string)($row['seccion_orden'] ?? 'varios'));
            if ($seccion === 'cuenta_comisiones') {
                $puntosCuentaComisiones[] = $puntoNormalizado;
            } elseif ($seccion === 'cuenta_gobernador' || $seccion === 'cuenta_intendente') {
                $puntosCuentaGobernador[] = $puntoNormalizado;
            } else {
                $puntosVarios[] = $puntoNormalizado;
            }
        }

        return array_merge(
            array_slice($puntosFijos, 0, 2),
            $puntosCuentaGobernador,
            array_slice($puntosFijos, 2, 1),
            $puntosCuentaComisiones,
            array_slice($puntosFijos, 3, 1),
            $puntosVarios
        );
    }

    public function crear(int $idSesion, string $puntoNumero, string $textoAcuerdo, int $idUsuario): array
    {
        $sesion = $this->obtenerSesion($idSesion);
        if (!$sesion) {
            return ['success' => false, 'mensaje' => 'La sesion indicada no existe.'];
        }

        $textoAcuerdo = trim($textoAcuerdo);
        if ($textoAcuerdo === '') {
            return ['success' => false, 'mensaje' => 'Debe ingresar el texto del acuerdo.'];
        }

        $punto = $this->buscarPunto($idSesion, $puntoNumero, $sesion);
        if (!$punto) {
            return ['success' => false, 'mensaje' => 'Debe seleccionar un punto valido.'];
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO pleno_acuerdos
                (id_sesion, id_sesion_plenaria_tema, punto_numero, titulo_punto, texto_acuerdo, estado, usuario_creador, fecha_creacion)
             VALUES
                (:id_sesion, :id_sesion_plenaria_tema, :punto_numero, :titulo_punto, :texto_acuerdo, 'vigente', :usuario_creador, NOW())"
        );
        $stmt->execute([
            ':id_sesion' => $idSesion,
            ':id_sesion_plenaria_tema' => $punto['id_sesion_plenaria_tema'],
            ':punto_numero' => $punto['punto_numero'],
            ':titulo_punto' => $punto['titulo_punto'],
            ':texto_acuerdo' => $textoAcuerdo,
            ':usuario_creador' => $idUsuario > 0 ? $idUsuario : null,
        ]);

        return [
            'success' => true,
            'mensaje' => 'Acuerdo registrado correctamente.',
            'id_acuerdo' => (int)$this->conn->lastInsertId(),
        ];
    }

    public function actualizar(int $idAcuerdo, string $textoAcuerdo, int $idUsuario): array
    {
        $textoAcuerdo = trim($textoAcuerdo);
        if ($idAcuerdo <= 0) {
            return ['success' => false, 'mensaje' => 'Debe indicar un acuerdo valido.'];
        }

        if ($textoAcuerdo === '') {
            return ['success' => false, 'mensaje' => 'Debe ingresar el texto del acuerdo.'];
        }

        $acuerdo = $this->obtenerVigente($idAcuerdo);
        if (!$acuerdo) {
            return ['success' => false, 'mensaje' => 'No se encontro un acuerdo vigente para editar.'];
        }

        $stmt = $this->conn->prepare(
            "UPDATE pleno_acuerdos
             SET texto_acuerdo = :texto_acuerdo,
                 usuario_actualizador = :usuario_actualizador,
                 fecha_actualizacion = NOW()
             WHERE id_acuerdo = :id_acuerdo
               AND estado = 'vigente'"
        );
        $stmt->execute([
            ':texto_acuerdo' => $textoAcuerdo,
            ':usuario_actualizador' => $idUsuario > 0 ? $idUsuario : null,
            ':id_acuerdo' => $idAcuerdo,
        ]);

        return ['success' => true, 'mensaje' => 'Acuerdo actualizado correctamente.'];
    }

    public function eliminar(int $idAcuerdo, int $idUsuario): array
    {
        if ($idAcuerdo <= 0) {
            return ['success' => false, 'mensaje' => 'Debe indicar un acuerdo valido.'];
        }

        $acuerdo = $this->obtenerVigente($idAcuerdo);
        if (!$acuerdo) {
            return ['success' => false, 'mensaje' => 'No se encontro un acuerdo vigente para eliminar.'];
        }

        $stmt = $this->conn->prepare(
            "UPDATE pleno_acuerdos
             SET estado = 'eliminado',
                 usuario_actualizador = :usuario_actualizador,
                 fecha_actualizacion = NOW(),
                 fecha_eliminacion = NOW()
             WHERE id_acuerdo = :id_acuerdo
               AND estado = 'vigente'"
        );
        $stmt->execute([
            ':usuario_actualizador' => $idUsuario > 0 ? $idUsuario : null,
            ':id_acuerdo' => $idAcuerdo,
        ]);

        return ['success' => true, 'mensaje' => 'Acuerdo eliminado correctamente.'];
    }

    private function obtenerSesion(int $idSesion): ?array
    {
        $stmt = $this->conn->prepare('SELECT * FROM sesiones_plenarias WHERE id_sesion = :id LIMIT 1');
        $stmt->execute([':id' => $idSesion]);
        $sesion = $stmt->fetch();

        return is_array($sesion) ? $sesion : null;
    }

    private function obtenerVigente(int $idAcuerdo): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT *
             FROM pleno_acuerdos
             WHERE id_acuerdo = :id_acuerdo
               AND estado = 'vigente'
             LIMIT 1"
        );
        $stmt->execute([':id_acuerdo' => $idAcuerdo]);
        $acuerdo = $stmt->fetch();

        return is_array($acuerdo) ? $acuerdo : null;
    }

    private function buscarPunto(int $idSesion, string $puntoNumero, array $sesion): ?array
    {
        $puntoNumero = trim($puntoNumero);
        foreach ($this->listarPuntosSesion($idSesion, $sesion) as $punto) {
            if ((string)$punto['punto_numero'] === $puntoNumero) {
                return $punto;
            }
        }

        return null;
    }

    private function resolverNumeroPunto(array $punto): string
    {
        $seccion = trim((string)($punto['seccion_orden'] ?? 'varios'));
        $orden = max(1, (int)($punto['orden'] ?? 1));

        if ($seccion === 'cuenta_comisiones') {
            return '3.' . $orden;
        }

        if ($seccion === 'cuenta_gobernador' || $seccion === 'cuenta_intendente') {
            return '2.' . $orden;
        }

        return '4.' . $orden;
    }

    private function resolverTituloPunto(array $punto): string
    {
        $tipoPunto = strtoupper(trim((string)($punto['tipo_punto'] ?? 'COMISION')));

        if ($tipoPunto === 'TABLA') {
            return trim((string)($punto['titulo_punto'] ?? '')) ?: 'Punto de tabla';
        }

        if ($tipoPunto === 'IMPREVISTA') {
            return trim((string)($punto['nombre_imprevista'] ?? '')) ?: 'Comision imprevista';
        }

        $comision = trim((string)($punto['nombreComision'] ?? ''));
        $tema = trim((string)($punto['nombreTema'] ?? ''));

        if ($tema !== '') {
            return $tema;
        }

        return $comision !== '' ? $comision : 'Punto de comision';
    }
}
