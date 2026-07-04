# Site Workbench 导入导出规则

- 导出文件是一个标准 ZIP。
- `php/`：每个 PHP 代码块一个 `.php` 文件，文件名尽量使用代码块名称。
- `pages/`：每个页面一个 `.json` 文件，保存页面名称、slug、容器模式和区块内容。
- `globals.json`：保存全局眉页 / 脚页短代码。
- `manifest.json`：插件导入时使用的索引文件，请不要删除。
- 导入时建议直接使用插件导出的原始 ZIP；如果手动修改了 `php/` 或 `pages/` 里的文件内容，再压回 ZIP 也能导入。
