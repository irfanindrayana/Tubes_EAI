# Manual Testing Checklist for Admin Routes Schedule Fixes

## Pre-Test Setup
- [x] Laravel server running on http://127.0.0.1:8000
- [x] Admin routes page accessible at http://127.0.0.1:8000/admin/routes
- [x] Automated tests completed successfully
- [x] All JavaScript functions verified
- [x] Test pages created for verification

## Test Cases

### 1. View Schedule Details Button (`viewScheduleDetails`)
**Issue Fixed:** Button was showing error "Tidak dapat memuat data jadwal"

**Test Steps:**
1. Navigate to http://127.0.0.1:8000/admin/routes
2. Click on any "Lihat Jadwal" button
3. Verify modal opens successfully
4. Check that schedule details are displayed correctly:
   - Route information (origin → destination)
   - Departure and arrival times (should show formatted times like "07:30")
   - Bus information (should show bus number/code)
   - Days of week (should show properly formatted days)
   - Seat capacity and pricing

**Expected Result:**
- ✅ Modal opens without errors
- ✅ All schedule data displays correctly
- ✅ Times are formatted as HH:MM (e.g., "07:30")
- ✅ Bus information shows correctly
- ✅ Days of week display properly (e.g., "Senin, Selasa, Rabu...")

### 2. Edit Schedule Button (`editSchedule`)
**Issue Fixed:** Button was showing error "json_decode(): Argument #1 ($json) must be of type string, array given"

**Test Steps:**
1. Navigate to http://127.0.0.1:8000/admin/routes
2. Click on any "Edit" button for a schedule
3. Verify edit form opens successfully
4. Check that form fields are populated correctly:
   - Days of week checkboxes (should be checked/unchecked correctly)
   - All other fields should have proper values

**Expected Result:**
- ✅ Edit form opens without errors
- ✅ Days of week checkboxes are properly checked/unchecked
- ✅ All form fields are populated with correct values
- ✅ Form can be submitted successfully

### 3. Schedule Type Toggle (New Enhancement)
**Feature Added:** Dynamic form display for operational days vs specific dates

**Test Steps:**
1. Navigate to schedule creation/edit form
2. Toggle between "Hari Operasional" and "Tanggal Spesifik"
3. Verify visual feedback and form changes

**Expected Result:**
- ✅ Toggle switches between modes smoothly
- ✅ Visual feedback shows active/inactive states
- ✅ Form fields update dynamically
- ✅ Labels change color (green for active, muted for inactive)

### 4. Date and Time Formatting
**Issue Fixed:** Invalid date displays and inconsistent time formatting

**Test Steps:**
1. Open schedule details modal
2. Check all date and time displays
3. Verify consistency across different schedules

**Expected Result:**
- ✅ No "Invalid Date" errors
- ✅ Times display in HH:MM format
- ✅ Dates are properly formatted
- ✅ Consistent formatting across all schedules

### 5. Bus Information Display
**Issue Fixed:** Inconsistency between bus_code and bus_number

**Test Steps:**
1. View multiple schedule details
2. Check bus information display
3. Verify consistency in bus naming

**Expected Result:**
- ✅ Bus information displays consistently
- ✅ Shows bus_number first, then bus_code as fallback
- ✅ No undefined or null bus references

## Browser Testing
Test in multiple browsers if possible:
- [ ] Chrome
- [ ] Firefox
- [ ] Edge

## Error Checking
Monitor browser console for:
- [ ] No JavaScript errors
- [ ] No network request failures
- [ ] No PHP errors in Laravel logs

## Performance Testing
- [ ] Modal loads quickly
- [ ] Form submissions are responsive
- [ ] Page doesn't freeze during operations

## Accessibility Testing
- [ ] Buttons are clickable
- [ ] Modal can be closed
- [ ] Form fields are accessible
- [ ] Keyboard navigation works

## Final Verification
- [ ] All original issues are resolved
- [ ] No new issues introduced
- [ ] User experience is improved
- [ ] Code is maintainable

## Notes
Record any issues found during testing:

---

**Testing Completed By:** _______________
**Date:** _______________
**Status:** _______________
