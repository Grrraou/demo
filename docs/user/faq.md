# Frequently Asked Questions

## General Questions

### Q: How do I reset my password?
A: Click "Forgot Password" on the login page and enter your email address. You'll receive a password reset link via email.

### Q: Can I access the system from multiple devices?
A: Yes, you can log in from multiple devices. Your data will sync automatically across all sessions.

### Q: What browsers are supported?
A: The system supports the latest versions of Chrome, Firefox, Safari, and Edge. We recommend keeping your browser updated for the best experience.

### Q: Is my data secure?
A: Yes, all data is encrypted and transmitted over secure HTTPS connections. We follow industry best practices for data security.

## Account Management

### Q: How do I change my email address?
A: Go to Settings → Profile → Edit Profile. Update your email address and save. You'll need to verify the new email.

### Q: Can I change my role or permissions?
A: Only administrators can change user roles and permissions. Contact your system administrator for role changes.

### Q: How do I switch between companies?
A: If you have access to multiple companies, use the company selector in the top navigation bar to switch between them.

### Q: What happens when I delete my account?
A: Deleting your account removes all your data and access. This action cannot be undone. Consider exporting your data first.

## Module-Specific Questions

### Accounting Module

**Q: How do I create a journal entry?**
A: Go to Accounting → Journal Entries → Create Entry. Select the date, accounts, and amounts. Ensure debits equal credits.

**Q: Can I edit posted journal entries?**
A: No, posted entries cannot be edited. Create a reversing entry or adjustment entry instead.

**Q: How do I view the trial balance?**
A: Navigate to Accounting → Trial Balance. Select the date range and click "Generate Report".

**Q: What's the difference between debit and credit?**
A: Debits increase asset and expense accounts, while credits increase liability, equity, and revenue accounts.

### Inventory Module

**Q: How do I add a new product?**
A: Go to Inventory → Products → Create Product. Fill in product details including SKU, name, category, and initial stock.

**Q: Can I track stock across multiple locations?**
A: Yes, create multiple stock locations and assign products to each location. Stock is tracked per location.

**Q: How do I handle stock adjustments?**
A: Use Stock Movements → Create Movement. Select adjustment type and enter the quantity change.

**Q: What's the difference between in-stock and available stock?**
A: In-stock is total quantity, while available stock subtracts reserved quantities for pending orders.

### Sales Module

**Q: How do I convert a quote to an order?**
A: Open the quote and click "Convert to Order". Review the details and save to create the order.

**Q: Can I modify an order after it's confirmed?**
A: Yes, you can modify orders until they're marked as "Shipped". Changes may affect inventory and billing.

**Q: How do I create an invoice from an order?**
A: Select the order and click "Create Invoice". The system will copy order details to the invoice.

**Q: What happens when I mark an order as delivered?**
A: The system updates stock levels, updates order status, and may trigger automated notifications.

### Customer Management

**Q: How do I add multiple contacts to a company?**
A: Create or open the customer company, then click "Add Contact" to add multiple contacts.

**Q: Can I import customers from a spreadsheet?**
A: Yes, use the import function in the customers module. Download the template, fill it with your data, and upload.

**Q: How do I merge duplicate customer records?**
A: Select the duplicate customers and click "Merge". Choose the master record and what data to keep.

**Q: Can customers access their own data?**
A: Currently, customers cannot access the system directly. You can export customer data for them if needed.

### Chat Module

**Q: How do I start a group conversation?**
A: Click "New Conversation" → "Group Chat" → Add participants → Set group name → Start chatting.

**Q: Can I share files in chat?**
A: Yes, click the attachment icon to upload and share files with participants.

**Q: How do I search old messages?**
A: Use the search bar in the conversation or use the global search to find messages across all conversations.

**Q: Are chat messages encrypted?**
A: Yes, all chat messages are encrypted in transit and at rest for security.

### Calendar Module

**Q: How do I create a recurring event?**
A: When creating an event, select "Recurring" and set the pattern (daily, weekly, monthly) and end date.

**Q: Can I share my calendar with others?**
A: Yes, go to Calendar Settings → Sharing → Add people and set their permission level.

**Q: How do I book a meeting room?**
A: Create an event and select the meeting room as a resource. The system will check availability.

**Q: Can I sync with external calendars?**
A: Currently, external calendar sync is not available. This feature is planned for future releases.

### Lead Management

**Q: How do I assign leads to team members?**
A: Open the lead and click "Assign". Select the team member and set their role in the lead.

**Q: What's the difference between a lead and a customer?**
A: Leads are potential customers being qualified. Once converted, they become customers in the main system.

**Q: How do I track lead conversion rates?**
A: Use the Lead Reports to view conversion rates by source, status, and time period.

**Q: Can I automate lead assignment?**
A: Yes, set up assignment rules in Lead Settings to automatically distribute leads based on criteria.

## Technical Questions

### Q: Why is the system running slowly?
A: Common causes include:
- Large data sets needing pagination
- Slow internet connection
- Browser cache issues
- High server load

Try clearing browser cache, reducing data per page, or contact support.

### Q: I'm getting a "Permission Denied" error. What should I do?
A: This means you don't have the required permissions. Contact your administrator to request access or check if you're in the correct company context.

### Q: How do I export data?
A: Most modules have an "Export" button. Choose your format (CSV, Excel, PDF) and date range, then download the file.

### Q: Can I customize the dashboard?
A: Yes, go to Dashboard → Customize. Add, remove, or rearrange widgets to suit your workflow.

### Q: What happens during system maintenance?
A: The system may be temporarily unavailable for updates. We'll notify users in advance of scheduled maintenance.

## Troubleshooting

### Q: I can't log in. What should I do?
A: Try these steps:
1. Check your email and password
2. Clear browser cache and cookies
3. Try a different browser
4. Reset your password
5. Contact support if issues persist

### Q: Forms aren't submitting. What's wrong?
A: Check for:
- Required fields (marked with *)
- Validation errors (red text)
- File size limits for uploads
- Network connectivity issues

### Q: Data isn't saving. Why?
A: Possible causes:
- Missing required fields
- Validation errors
- Network timeout
- Permission issues
- Concurrent editing conflicts

### Q: Reports are loading slowly. How can I speed this up?
A: Try:
- Reducing the date range
- Using filters to limit data
- Exporting data instead of viewing online
- Running reports during off-peak hours

## Data Management

### Q: How often is data backed up?
A: Data is backed up automatically every 24 hours. Additional backups are created before major updates.

### Q: Can I restore deleted data?
A: Deleted data can be restored within 30 days. After that, it's permanently removed.

### Q: How do I export all my data?
A: Go to Settings → Data Management → Export All Data. This creates a complete export of your company data.

### Q: Is there a data limit?
A: There's no hard limit, but very large datasets may affect performance. Consider archiving old data regularly.

## Integration Questions

### Q: Can I integrate with other systems?
A: Yes, the system provides REST APIs for integration. See the developer documentation for details.

### Q: Is there a mobile app?
A: Currently, the system is web-based and works well on mobile browsers. A native mobile app is planned.

### Q: Can I use custom domains?
A: Yes, enterprise plans support custom domains and white-labeling.

### Q: Does it support single sign-on (SSO)?
A: SSO is available for enterprise customers. Contact support for setup instructions.

## Billing and Plans

### Q: How do I upgrade my plan?
A: Go to Settings → Billing → Upgrade Plan. Choose your new plan and follow the payment process.

### Q: Can I cancel my subscription?
A: Yes, you can cancel anytime. Your data will be available until the end of your billing period.

### Q: What happens to my data if I cancel?
A: You can export your data before cancellation. After 30 days, data is permanently deleted.

### Q: Are there any hidden fees?
A: No, all pricing is transparent. Additional costs only apply for premium features or custom development.

## Still Need Help?

If you couldn't find an answer to your question:

1. **Check the documentation** - Browse the detailed guides for each module
2. **Contact your administrator** - They can help with account and permission issues
3. **Submit a support ticket** - For technical issues and feature requests
4. **Join our community** - Connect with other users for tips and best practices
5. **Schedule a training session** - We offer personalized training for teams

---

**Last Updated:** February 2026  
**Version:** 1.0

For the most up-to-date information, visit our online help center or contact support.
