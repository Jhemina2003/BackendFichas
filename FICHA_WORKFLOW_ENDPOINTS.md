# Endpoints de Workflow de Fichas

## Cambios Implementados

### 🎯 **Endpoints Simplificados para Ventanillas**

Se eliminó la necesidad de especificar el ID de la ficha en las rutas. Ahora los endpoints trabajan automáticamente con la ficha activa de la ventanilla autenticada.

### **Antes** ❌
```
POST /api/fichas/{ficha}/en-atencion
POST /api/fichas/{ficha}/finalizar
POST /api/fichas/{ficha}/ausente
POST /api/fichas/{ficha}/observacion
POST /api/fichas/{ficha}/redirigir
```

### **Ahora** ✅
```
POST /api/ventanilla/rellamar
POST /api/ventanilla/en-atencion
POST /api/ventanilla/finalizar
POST /api/ventanilla/ausente
POST /api/ventanilla/observacion
POST /api/ventanilla/redirigir
```

## 🔒 **Autenticación y Autorización**

- **Middleware**: `auth:sanctum` en todos los endpoints
- **Autorización automática**: Solo la ventanilla que tiene una ficha activa puede modificar su estado
- **Validación de estados**: Verificación automática de transiciones válidas

## 🔄 **Flujo de Estados de Ficha**

```
en_espera → llamado → en_atencion → finalizado/ausente/reasignado
```

### **Detalle de Endpoints**

#### 1. **Rellamar Ficha**
- **Ruta**: `POST /api/ventanilla/rellamar`
- **Función**: Llama a la siguiente ficha en espera para la ventanilla
- **Lógica**: Alterna 2 fichas preferenciales por cada 2 normales
- **Estado resultante**: `llamado`

#### 2. **Marcar en Atención**
- **Ruta**: `POST /api/ventanilla/en-atencion`
- **Requisito**: Ficha debe estar en estado `llamado`
- **Estado resultante**: `en_atencion`

#### 3. **Finalizar Ficha**
- **Ruta**: `POST /api/ventanilla/finalizar`
- **Requisito**: Ficha debe estar en estado `en_atencion`
- **Estado resultante**: `finalizado`

#### 4. **Marcar Ausente**
- **Ruta**: `POST /api/ventanilla/ausente`
- **Requisito**: Ficha debe estar en estado `llamado`
- **Parámetros**: `observacion` (opcional)
- **Estado resultante**: `ausente`

#### 5. **Agregar Observación y Finalizar**
- **Ruta**: `POST /api/ventanilla/observacion`
- **Requisito**: Ficha debe estar en estado `en_atencion`
- **Parámetros**: `observacion` (requerido, mínimo 5 caracteres)
- **Estado resultante**: `finalizado`

#### 6. **Redirigir Ficha**
- **Ruta**: `POST /api/ventanilla/redirigir`
- **Requisito**: Ficha debe estar en estado `en_atencion`
- **Parámetros**: `justificativo` (requerido, mínimo 5 caracteres)
- **Estados resultantes**: `reasignado` → `finalizado`

## 🏗️ **Arquitectura Técnica**

### **Método Helper: `getFichaActualVentanilla()`**
```php
private function getFichaActualVentanilla($usuario)
{
    return \App\Models\Ficha::whereHas('seguimientos', function($query) use ($usuario) {
        $query->where('fk_usuario_id', $usuario->usuario_id)
              ->whereHas('dominioEstado', function($q) {
                  $q->whereIn('nombre', ['llamado', 'en_atencion']);
              });
    })
    ->whereIn('estado_actual', ['llamado', 'en_atencion'])
    ->orderByDesc('updated_at')
    ->first();
}
```

### **Ventajas del Nuevo Enfoque**

1. **UX Mejorada**: No es necesario especificar IDs de ficha
2. **Seguridad**: Autorización automática basada en la ventanilla autenticada
3. **Simplicidad**: Los endpoints son más intuitivos y fáciles de usar
4. **Consistencia**: Todos los endpoints siguen el mismo patrón
5. **Eficiencia**: Detección automática de la ficha activa

### **Migración de APIs Existentes**

Las siguientes rutas se mantienen para funcionalidades específicas:
- `POST /api/fichas/{ficha}/reasignar` - Para reasignación administrativa
- `GET /api/fichas/{ficha}/historial` - Para consultar historial específico

## 🧪 **Testing**

Para probar los endpoints, usar el token de autenticación Sanctum:

```bash
# Ejemplo de uso
curl -X POST /api/ventanilla/rellamar \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json"

curl -X POST /api/ventanilla/observacion \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"observacion": "Cliente atendido satisfactoriamente"}'
```

## ✅ **Estado Actual**

- ✅ Endpoints implementados y funcionando
- ✅ Autenticación Sanctum configurada
- ✅ Autorización por ventanilla implementada
- ✅ Validación de estados de ficha
- ✅ Rutas registradas en `routes/api.php`
- ✅ Métodos actualizados en `SeguimientoController`
- ✅ Sin errores de compilación

El sistema está listo para ser usado por las ventanillas con una experiencia de usuario simplificada y segura.