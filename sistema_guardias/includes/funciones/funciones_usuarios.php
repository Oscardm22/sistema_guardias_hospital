<?php
/**
 * Funciones para gestión de usuarios del sistema de guardias hospitalarias
 * 
 * Adaptado para usar MySQLi según tu archivo conexion.php
 */

require_once __DIR__ . '/../conexion.php';

class UsuarioFunciones {
    /**
     * Obtiene el nombre para mostrar del usuario actual
     */
    public static function nombreMostrar($conn) {
        if (!isset($_SESSION['usuario_id'])) {
            return 'Invitado';
        }
        
        // Si es admin
        if ($_SESSION['rol'] === 'admin') {
            return 'Administrador del Sistema';
        }
        
        // Para usuarios regulares (personal médico)
        $query = "SELECT nombre, apellido FROM personal WHERE id_personal = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $_SESSION['usuario_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($personal = $result->fetch_assoc()) {
            return $personal['nombre'] . ' ' . $personal['apellido'];
        }
        
        return 'Usuario General';
    }

    /**
     * Obtiene el rol formateado para mostrar
     */
    public static function rolMostrar() {
        if (!isset($_SESSION['rol'])) {
            return 'Invitado';
        }
        
        return $_SESSION['rol'] === 'admin' ? 'Administrador' : 'Personal Médico';
    }

    /**
     * Verifica si el usuario actual es administrador
     */
    public static function esAdmin() {
        return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
    }

    /**
     * Valida las credenciales de un usuario
     */
    public static function validarCredenciales($conn, $usuario, $contrasena) {
        $query = "SELECT id_usuario, contrasena, rol FROM usuarios WHERE usuario = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            if (password_verify($contrasena, $usuario['contrasena'])) {
                return $usuario;
            }
        }
        return false;
    }

    /**
     * Obtiene información de un usuario por ID
     */
    public static function obtenerUsuario($conn, $idUsuario) {
        $query = "SELECT id_usuario, usuario, rol FROM usuarios WHERE id_usuario = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Actualiza la última actividad del usuario
     */
    public static function registrarActividad($conn, $idUsuario) {
        $query = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id_usuario = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $idUsuario);
        return $stmt->execute();
    }

    /**
     * Cambia la contraseña de un usuario
     */
    public static function cambiarContrasena($conn, $idUsuario, $nuevaContrasena) {
        $hash = password_hash($nuevaContrasena, PASSWORD_BCRYPT);
        $query = "UPDATE usuarios SET contrasena = ? WHERE id_usuario = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $hash, $idUsuario);
        return $stmt->execute();
    }

    /**
     * Lista todos los usuarios del sistema
     */
    public static function listarUsuarios($conn) {
        $query = "SELECT id_usuario, usuario, rol FROM usuarios";
        $result = $conn->query($query);
        
        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }
        return $usuarios;
    }

    /**
     * Crea un nuevo usuario
     */
    public static function crearUsuario($conn, $usuario, $contrasena, $rol) {
        $hash = password_hash($contrasena, PASSWORD_BCRYPT);
        $query = "INSERT INTO usuarios (usuario, contrasena, rol) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $usuario, $hash, $rol);
        return $stmt->execute();
    }

    /**
     * Actualiza un usuario existente
     */
    public static function actualizarUsuario($conn, $idUsuario, $usuario, $rol, $contrasena = null) {
        if ($contrasena) {
            $hash = password_hash($contrasena, PASSWORD_BCRYPT);
            $query = "UPDATE usuarios SET usuario = ?, rol = ?, contrasena = ? WHERE id_usuario = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $usuario, $rol, $hash, $idUsuario);
        } else {
            $query = "UPDATE usuarios SET usuario = ?, rol = ? WHERE id_usuario = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $usuario, $rol, $idUsuario);
        }
        return $stmt->execute();
    }

    /**
     * Elimina un usuario
     */
    public static function eliminarUsuario($conn, $idUsuario) {
        $query = "DELETE FROM usuarios WHERE id_usuario = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $idUsuario);
        return $stmt->execute();
    }

    /**
     * Verifica si un nombre de usuario ya existe
     */
    public static function existeUsuario($conn, $usuario, $excluirId = null) {
    $count = 0; // Inicializamos la variable
    
    if ($excluirId) {
        $query = "SELECT COUNT(*) FROM usuarios WHERE usuario = ? AND id_usuario != ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $usuario, $excluirId);
    } else {
        $query = "SELECT COUNT(*) FROM usuarios WHERE usuario = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $usuario);
    }
    
    if ($stmt->execute()) {
        $stmt->bind_result($count);
        $stmt->fetch();
    }
    
    return $count > 0;
    }
}

function nombre_completo_usuario() {
    if (!isset($_SESSION['usuario'])) {
        return 'Invitado';
    }
    return $_SESSION['usuario']['rol'] === 'admin' 
        ? 'Administrador del Sistema' 
        : 'Usuario General';
}