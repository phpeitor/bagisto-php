# Estándares de Base de Datos — etlweb (Bagisto)

MySQL (`pdo_mysql`), acceso directo disponible en este entorno vía `artisan tinker` sobre la **base de datos de producción** (`metadatape.com`). Tratar cada operación como productiva — no hay entorno de staging separado accesible desde aquí.

## Migraciones

- Ubicación: `packages/Webkul/{Paquete}/src/Database/Migrations/`, cargadas por cada `ServiceProvider::boot()` (`loadMigrationsFrom`). Cada paquete gestiona sus propias tablas.
- Nombrado: `YYYY_MM_DD_HHMMSS_verbo_descripcion.php`, igual que Laravel estándar.
- **Idempotencia obligatoria**: Bagisto se instala/actualiza corriendo migraciones de múltiples paquetes en distintos entornos; toda migración que agrega columna/índice debe chequear existencia antes:

```php
Schema::table('product_price_indices', function (Blueprint $table) {
    if (! Schema::hasIndex('product_price_indices', 'ppi_product_id_customer_group_id_idx')) {
        $table->index(['product_id', 'customer_group_id'], 'ppi_product_id_customer_group_id_idx');
    }
});
```

- `down()` siempre simétrico y también con guard (`if (Schema::hasIndex(...)) dropIndex(...)`), nunca asumir que el estado previo existe.
- Nombres de índice: prefijo corto de tabla + columnas + sufijo `_idx` (`ppi_product_id_customer_group_id_idx`, `pc_product_id_channel_id_idx`). Mantener ese patrón para que sea buscable por tabla.
- No usar `Schema::drop`/`dropColumn` sobre tablas con datos reales sin antes confirmar con el usuario — es irreversible en producción sin backup propio.

## Modelo EAV (productos)

- No escribir queries crudas contra `product_attribute_values`/`product_flat` desde controllers o scripts puntuales. `product_flat` es una tabla **derivada** (una fila por producto+canal+locale) que se reconstruye vía indexers (`php artisan indexer:index`); escribir directo ahí se pierde en el próximo reindex.
- Cambios de atributos de producto van por `ProductRepository`/`ProductAttributeValueRepository`, no por `DB::table('product_attribute_values')->update(...)` salvo debugging puntual y no persistente.

## Configuración (`core_config`)

- Tabla key/value: `code` (string, dot-notation tipo `catalog.products.attribute.file_attribute_upload_size`), `value`, `channel_code` opcional, `locale_code` opcional.
- Antes de un `UPDATE` manual vía tinker: `SELECT` primero la fila para confirmar que existe y ver el valor actual (evita crear filas duplicadas con distinto scope por error).
- Después de escribir, `php artisan cache:clear` — estos valores se cachean.

## Traducciones (`*_translations`)

- Patrón `astrotomic/laravel-translatable`: tabla base (`channels`, `products`, `categories`, `cms_pages`, ...) + tabla `_translations` con `locale` + FK al id base.
- Al diagnosticar "el contenido no cambia por idioma", verificar primero si existe fila en `_translations` para ese `locale` y si tiene el campo relevante lleno — es común que un locale nuevo (`en`) quede con el placeholder/demo original de Bagisto en vez del contenido real (pasó con `channel_translations.home_seo` de `en`, tenía "Demo store").

## Operaciones directas vía `artisan tinker` en este entorno

Dado que este entorno tiene acceso directo a la BD de producción:

1. **Leer antes de escribir**: `SELECT`/`DB::table(...)->where(...)->get()` para confirmar la fila objetivo antes de cualquier `update`/`insert`/`delete`.
2. Preferir `DB::table(...)->update([...])` acotado por PK o `where` específico — nunca updates masivos sin `where` en producción.
3. Confirmar el resultado con otro `SELECT` después de escribir.
4. Limpiar los cachés que correspondan (`config:clear`, `cache:clear`, `optimize:clear`) — un `UPDATE` exitoso que no se refleja casi siempre es caché, no un fallo del write.
5. Para cambios de datos que un futuro admin podría querer repetir/auditar, documentarlos en `CAMBIOS_IMPLEMENTADOS.md` igual que un cambio de código — no son "solo datos", son parte del comportamiento del sitio.

## Charset / collation

- Seguir el default ya establecido por las tablas existentes del proyecto (utf8mb4) — no fijar collation distinta por tabla nueva sin razón explícita.
