#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
COMPOSE_FILE="${ASE_WPORG_COMPOSE_FILE:-$SCRIPT_DIR/docker-compose.wporg.yml}"
PROJECT="${ASE_WPORG_PROJECT:-ase-wporg}"
PORT="${ASE_WPORG_PORT:-8098}"
BASE_URL="${ASE_WPORG_BASE_URL:-http://localhost:$PORT}"

compose() {
	docker compose -p "$PROJECT" -f "$COMPOSE_FILE" "$@"
}

wp() {
	compose run --rm cli wp "$@"
}

compose up -d db wordpress

for _ in $(seq 1 90); do
	if curl -fsS "$BASE_URL/wp-admin/install.php" >/dev/null 2>&1 || curl -fsS "$BASE_URL" >/dev/null 2>&1; then
		break
	fi
	sleep 2
done

if ! wp core is-installed >/dev/null 2>&1; then
	wp core install \
		--url="$BASE_URL" \
		--title="Arabic Search Enhancement QA" \
		--admin_user=admin \
		--admin_password=password \
		--admin_email=admin@example.com \
		--skip-email
fi

wp plugin activate arabic-search-enhancement
wp option update blog_public 0
wp option update arabseen_enable_enhancement 1
wp option update arabseen_search_excerpt 1
wp option update arabseen_analytics_enabled 1
wp option update permalink_structure '/%postname%/'

wp post delete 1 --force >/dev/null 2>&1 || true
wp post list --post_type=post --format=ids | xargs -r wp post delete --force >/dev/null 2>&1 || true
wp post list --post_type=page --format=ids | xargs -r wp post delete --force >/dev/null 2>&1 || true

wp post create \
	--post_type=post \
	--post_status=publish \
	--post_title='فوائد قراءة القرآن الكريم' \
	--post_content='هذا مقال تجريبي يحتوي على كلمة قرآن مع الهمزة لاختبار البحث عن قران بدون همزة.'

wp post create \
	--post_type=post \
	--post_status=publish \
	--post_title='اللغة العربية وتشكيل الكلمات' \
	--post_content='نص تجريبي يوضح كيف يساعد تحسين البحث العربي في العثور على الكلمات مع اختلاف التشكيل.'

wp post create \
	--post_type=page \
	--post_status=publish \
	--post_title='دليل البحث العربي' \
	--post_content='صفحة اختبار تعرض أمثلة على تطبيع الألف والياء والتاء المربوطة في نتائج البحث.'

wp rewrite flush --hard

echo "$BASE_URL"
