#!/bin/bash

echo "🚀 Laravel Multi-Dashboard Starter Kit - System Test"
echo "=================================================="

echo "📊 Testing Dashboard Creation..."

# Create a doctor dashboard
echo "Creating doctor dashboard..."
php artisan dashboard:create doctor \
  --name="Doctor Dashboard" \
  --description="Dashboard for medical doctors" \
  --auth=both \
  --theme=green \
  --roles=admin,doctor,resident \
  --permissions=view_patients,manage_appointments,prescribe_medication \
  --force

echo "✅ Doctor dashboard created successfully!"

# Create a nurse dashboard
echo "Creating nurse dashboard..."
php artisan dashboard:create nurse \
  --name="Nurse Dashboard" \
  --description="Dashboard for nursing staff" \
  --auth=email \
  --theme=purple \
  --roles=admin,head_nurse,staff_nurse \
  --permissions=view_patients,update_records,manage_medication \
  --force

echo "✅ Nurse dashboard created successfully!"

# Create a manufacturer dashboard
echo "Creating manufacturer dashboard..."
php artisan dashboard:create manufacturer \
  --name="Manufacturer Dashboard" \
  --description="Dashboard for manufacturing companies" \
  --auth=email \
  --theme=orange \
  --roles=admin,production_manager,quality_controller \
  --permissions=manage_production,quality_control,view_reports \
  --force

echo "✅ Manufacturer dashboard created successfully!"

echo ""
echo "🎯 Dashboard Creation Complete!"
echo "================================"
echo ""
echo "📱 Available Dashboards:"
echo "• Master Admin: http://localhost:8000/master-admin"
echo "• Doctor: http://localhost:8000/doctor/login"
echo "• Nurse: http://localhost:8000/nurse/login"
echo "• Manufacturer: http://localhost:8000/manufacturer/login"
echo ""
echo "📚 API Endpoints:"
echo "• Doctor API: http://localhost:8000/api/doctor/"
echo "• Nurse API: http://localhost:8000/api/nurse/"
echo "• Manufacturer API: http://localhost:8000/api/manufacturer/"
echo ""
echo "🔧 Testing Commands:"
echo "• Test SMS: curl -X POST http://localhost:8000/api/doctor/send-otp -d '{\"phone\":\"9843223774\"}'"
echo "• Test Login: curl -X POST http://localhost:8000/api/doctor/login -d '{\"email\":\"admin@doctor.com\",\"password\":\"password\"}'"
echo ""
echo "✨ System is ready for use!"