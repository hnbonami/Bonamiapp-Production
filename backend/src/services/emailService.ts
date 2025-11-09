private async generateStandardPerformanceEmailContent(
    user: any,
    report: any,
    organisation: any
  ): Promise<string> {
    return `
      <!DOCTYPE html>
      <html>
        <head>
          <style>
            body {
              font-family: Arial, sans-serif;
              line-height: 1.6;
              color: #333;
            }
            .container {
              max-width: 600px;
              margin: 0 auto;
              padding: 20px;
            }
            .header {
              background-color: #4CAF50;
              color: white;
              padding: 20px;
              text-align: center;
            }
            .content {
              padding: 20px;
              background-color: #f9f9f9;
            }
            .metric {
              background-color: white;
              padding: 15px;
              margin: 10px 0;
              border-left: 4px solid #4CAF50;
            }
            .footer {
              text-align: center;
              padding: 20px;
              font-size: 12px;
              color: #666;
            }
          </style>
        </head>
        <body>
          <div class="container">
            <div class="header">
              <h1>${organisation.name}</h1>
              <p>Performance Report</p>
            </div>
            <div class="content">
              <p>Hi ${user.name},</p>
              <p>Here is your performance report for ${report.reportDate.toLocaleDateString()}.</p>
              
              <div class="metric">
                <h3>Score: ${report.score || 'N/A'}</h3>
              </div>
              
              <div class="metric">
                <h3>Status: ${report.status || 'N/A'}</h3>
              </div>
              
              ${report.notes ? `
                <div class="metric">
                  <h3>Notes:</h3>
                  <p>${report.notes}</p>
                </div>
              ` : ''}
              
              <p>Keep up the good work!</p>
            </div>
            <div class="footer">
              <p>© ${new Date().getFullYear()} ${organisation.name}. All rights reserved.</p>
              <p>This is an automated message. Please do not reply to this email.</p>
            </div>
          </div>
        </body>
      </html>
    `;
  }